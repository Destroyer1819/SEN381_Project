<?php

namespace App\Services;

use App\Events\RequestStatusChanged;
use App\Exceptions\StaleRequestException;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestStatusService
{
    // The transition table is the single source of legal moves (D-015).
    public function allowedNext(string $from): array
    {
        return DB::table('request_status_transitions')
            ->where('old_status', $from)
            ->pluck('new_status')
            ->all();
    }

    public function transition(ServiceRequest $request, string $to, int $version, User $actor, ?string $note = null): void
    {
        $from = $request->status;
        $allowed = $this->allowedNext($from);

        // Friendly message here; the history table's foreign key is the backstop.
        if (! in_array($to, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'This request can only move to: '.implode(', ', $allowed).'.',
            ]);
        }

        $changes = [
            'status' => $to,
            'version' => DB::raw('version + 1'),
            'updated_at' => now(),
        ];
        if ($to === 'resolved') {
            $changes['resolved_at'] = now();
        }
        if ($from === 'resolved' && $to === 'in_progress') {
            $changes['resolved_at'] = null; // re-opened, so it is no longer resolved
        }

        DB::transaction(function () use ($request, $from, $to, $version, $actor, $note, $changes) {
            // Only succeeds if nobody changed the request since the page loaded
            $updated = ServiceRequest::whereKey($request->id)
                ->where('version', $version)
                ->update($changes);

            if ($updated === 0) {
                throw new StaleRequestException;
            }

            $request->history()->create([
                'old_status' => $from,
                'new_status' => $to,
                'changed_by' => $actor->id,
            ]);

            if ($note !== null) {
                $request->comments()->create([
                    'author_id' => $actor->id,
                    'comment' => $note,
                    'is_resolution' => true,
                ]);
            }

            // ShouldDispatchAfterCommit holds this until the transaction commits; a rollback drops it (D-009)
            RequestStatusChanged::dispatch(
                $request->id,
                'CC-'.$request->reference_number,
                $from,
                $to,
                $actor->id,
                now()->toIso8601String(),
                $request->requestor->email,
            );
        });
    }
}
