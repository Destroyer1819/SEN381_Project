<?php

namespace App\Services;

use App\Models\RequestAssignment;
use App\Models\RequestComment;
use App\Models\RequestStatusHistory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * All state changes to a service request go through this class so the
 * controlled workflow (FR-005), audit trail and optimistic locking live in one place.
 *
 * Workflow source of truth: the request_status_transition table.
 * Concurrency: service_request.version (optimistic locking).
 */
class RequestWorkflow
{
    // ---------- authorisation helpers ----------

    public function canView(User $user, ServiceRequest $request): bool
    {
        return $user->isStaffOrManagement() || $request->requestor_id === $user->id;
    }

    public function canManage(User $user, ServiceRequest $request): bool
    {
        return $user->isManagement() || ($user->isStaff() && $request->assigned_to === $user->id);
    }

    /** Statuses the user may move this request to from its current status. */
    public function availableTransitions(User $user, ServiceRequest $request): array
    {
        if (! $this->canManage($user, $request)) {
            return [];
        }

        return DB::table('request_status_transition')
            ->where('old_status', $request->status)
            ->where('new_status', '<>', 'assigned') // reaching "assigned" is only possible through assignment
            ->orderBy('new_status')
            ->pluck('new_status')
            ->all();
    }

    public function transitionAllowed(string $from, string $to): bool
    {
        return DB::table('request_status_transition')
            ->where('old_status', $from)
            ->where('new_status', $to)
            ->exists();
    }

    // ---------- FR-001: create ----------

    public function create(User $requestor, array $data): ServiceRequest
    {
        return DB::transaction(function () use ($requestor, $data) {
            // Serialise reference-number allocation so two simultaneous submissions cannot collide.
            DB::select('select pg_advisory_xact_lock(?)', [381001]);
            $reference = (int) (ServiceRequest::max('reference_number') ?? 1000) + 1;

            $request = ServiceRequest::create([
                'reference_number' => $reference,
                'requestor_id' => $requestor->id,
                'category_id' => $data['category_id'],
                'location' => $data['location'],
                'area' => $data['area'],
                'description' => $data['description'],
                'status' => 'open',
                'version' => 0,
            ]);

            $this->recordHistory($request->id, null, 'open', $requestor->id);

            return $request;
        });
    }

    // ---------- FR-005: controlled status change ----------

    public function changeStatus(ServiceRequest $request, string $to, User $actor, int $expectedVersion, ?string $note = null): ServiceRequest
    {
        if (! $this->canManage($actor, $request)) {
            throw new AuthorizationException('You cannot change the status of this request.');
        }

        if ($to === 'assigned') {
            throw ValidationException::withMessages(['status' => 'A request becomes "assigned" through assignment, not a manual status change.']);
        }

        if (! $this->transitionAllowed($request->status, $to)) {
            throw ValidationException::withMessages([
                'status' => "Invalid change: a request cannot move from \"{$request->status}\" to \"{$to}\".",
            ]);
        }

        $note = $note !== null ? trim($note) : null;
        if ($to === 'resolved' && ($note === null || $note === '')) {
            throw ValidationException::withMessages(['note' => 'A resolution note is required when resolving a request.']);
        }

        return DB::transaction(function () use ($request, $to, $actor, $expectedVersion, $note) {
            $from = $request->status;

            $affected = ServiceRequest::where('id', $request->id)
                ->where('version', $expectedVersion)
                ->where('status', $from)
                ->update([
                    'status' => $to,
                    'version' => DB::raw('version + 1'),
                    'updated_at' => now(),
                    'resolved_at' => match ($to) {
                        'resolved' => now(),
                        'in_progress' => null, // re-opened
                        default => $request->resolved_at,
                    },
                ]);

            if ($affected === 0) {
                throw ValidationException::withMessages([
                    'request' => 'This request was changed by someone else. Reload the page and try again.',
                ]);
            }

            $this->recordHistory($request->id, $from, $to, $actor->id);

            if ($note !== null && $note !== '') {
                $this->comment($request->id, $actor->id, $note, $to === 'resolved');
            }

            return $request->fresh();
        });
    }

    // ---------- FR-004: assignment ----------

    /** Staff member takes an open request for themselves. */
    public function selfAssign(ServiceRequest $request, User $actor): ServiceRequest
    {
        if (! $actor->isStaff()) {
            throw new AuthorizationException('Only staff can take a request.');
        }

        return DB::transaction(function () use ($request, $actor) {
            $this->moveToAssigned($request, $actor, $actor);

            RequestAssignment::create([
                'request_id' => $request->id,
                'assigned_to' => $actor->id,
                'offered_by' => $actor->id,
                'created_at' => now(),
                'state' => 'self_assigned',
            ]);

            return $request->fresh();
        });
    }

    /** Staff/management offers an open request to another staff member, who then accepts or declines. */
    public function offer(ServiceRequest $request, User $actor, User $target): RequestAssignment
    {
        if (! $actor->isStaffOrManagement()) {
            throw new AuthorizationException('Only staff or management can offer a request.');
        }
        if (! $target->isStaff()) {
            throw ValidationException::withMessages(['assigned_to' => 'A request can only be offered to a staff member.']);
        }
        if ($target->id === $actor->id) {
            throw ValidationException::withMessages(['assigned_to' => 'Use "Take this request" to assign it to yourself.']);
        }
        if ($request->status !== 'open') {
            throw ValidationException::withMessages(['request' => 'Only open requests can be offered.']);
        }

        $alreadyPending = RequestAssignment::where('request_id', $request->id)
            ->where('assigned_to', $target->id)
            ->where('state', 'offered')
            ->exists();
        if ($alreadyPending) {
            throw ValidationException::withMessages(['assigned_to' => 'This staff member already has a pending offer for this request.']);
        }

        return RequestAssignment::create([
            'request_id' => $request->id,
            'assigned_to' => $target->id,
            'offered_by' => $actor->id,
            'created_at' => now(),
            'state' => 'offered',
        ]);
    }

    public function respondToOffer(RequestAssignment $assignment, User $actor, bool $accept): RequestAssignment
    {
        if ($assignment->assigned_to !== $actor->id) {
            throw new AuthorizationException('This offer was not made to you.');
        }
        if ($assignment->state !== 'offered') {
            throw ValidationException::withMessages(['request' => 'This offer has already been answered.']);
        }

        return DB::transaction(function () use ($assignment, $actor, $accept) {
            if ($accept) {
                $this->moveToAssigned($assignment->request, $actor, $actor);
            }

            $assignment->update([
                'state' => $accept ? 'accepted' : 'declined',
                'responded_at' => now(),
            ]);

            return $assignment->fresh();
        });
    }

    private function moveToAssigned(ServiceRequest $request, User $assignee, User $actor): void
    {
        // Guarded update: succeeds only if nobody assigned the request in the meantime.
        $affected = ServiceRequest::where('id', $request->id)
            ->where('status', 'open')
            ->update([
                'status' => 'assigned',
                'assigned_to' => $assignee->id,
                'version' => DB::raw('version + 1'),
                'updated_at' => now(),
            ]);

        if ($affected === 0) {
            throw ValidationException::withMessages(['request' => 'This request is no longer open for assignment.']);
        }

        $this->recordHistory($request->id, 'open', 'assigned', $actor->id);
    }

    // ---------- comments ----------

    public function addComment(ServiceRequest $request, User $author, string $text): RequestComment
    {
        if (! $this->canView($author, $request)) {
            throw new AuthorizationException('You cannot comment on this request.');
        }

        return $this->comment($request->id, $author->id, trim($text), false);
    }

    private function comment(int $requestId, int $authorId, string $text, bool $isResolution): RequestComment
    {
        return RequestComment::create([
            'request_id' => $requestId,
            'author_id' => $authorId,
            'comment' => $text,
            'is_resolution' => $isResolution,
        ]);
    }

    private function recordHistory(int $requestId, ?string $from, string $to, int $changedBy): void
    {
        RequestStatusHistory::create([
            'request_id' => $requestId,
            'old_status' => $from,
            'new_status' => $to,
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);
    }
}
