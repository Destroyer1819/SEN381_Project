<?php

namespace App\Services;

use App\Events\RequestStatusChanged;
use App\Exceptions\OfferUnavailableException;
use App\Models\RequestAssignment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// D-018: offers, declines and the 3-declines-per-month rule.
class OfferService
{
    public const DECLINE_LIMIT = 3;

    // Who can receive an offer, in the order "next person" is picked: fewest open
    // assignments, then lowest id. Skips the offerer and anyone who declined this request.
    public function eligibleStaff(ServiceRequest $request, User $offerer): Collection
    {
        $declined = RequestAssignment::where('request_id', $request->id)
            ->where('state', 'declined')
            ->select('assigned_to');

        // select() before withCount(), otherwise withCount() loads every column (password hash included)
        return User::select(['id', 'name'])
            ->where('role', 'staff')
            ->whereKeyNot($offerer->id)
            ->whereNotIn('id', $declined)
            ->withCount(['assignedRequests as open_load' => fn ($q) => $q->whereIn('status', ['assigned', 'in_progress'])])
            ->orderBy('open_load')
            ->orderBy('id')
            ->get();
    }

    // Calendar month in APP_TIMEZONE, so the count resets on the 1st by itself
    public function declinesThisMonth(User $staff): int
    {
        return RequestAssignment::where('assigned_to', $staff->id)
            ->where('state', 'declined')
            ->where('responded_at', '>=', now()->startOfMonth())
            ->count();
    }

    // Returns the row written for $to: 'offered', or 'accepted' if they had no declines left
    public function offer(ServiceRequest $request, User $to, User $offerer): RequestAssignment
    {
        return DB::transaction(function () use ($request, $to, $offerer) {
            $this->lockOpenRequest($request);

            return $this->sendTo($request, $to, $offerer);
        });
    }

    // On decline, returns the row for the next person, or null if the request went back to the open pool
    public function respond(RequestAssignment $offer, User $staff, string $decision): ?RequestAssignment
    {
        return DB::transaction(function () use ($offer, $staff, $decision) {
            $request = ServiceRequest::whereKey($offer->request_id)->lockForUpdate()->firstOrFail();
            $offer = RequestAssignment::whereKey($offer->id)->lockForUpdate()->firstOrFail();

            if ($offer->state !== 'offered') {
                throw new OfferUnavailableException('This offer has already been answered.');
            }

            if ($decision === 'accept') {
                $offer->update(['state' => 'accepted', 'responded_at' => now()]);
                $this->assign($request, $staff, $staff);

                return null;
            }

            // Lock the staff member's row so two declines at the same moment cannot both see a count of 2
            User::whereKey($staff->id)->lockForUpdate()->first();

            if ($this->declinesThisMonth($staff) >= self::DECLINE_LIMIT) {
                throw new OfferUnavailableException('You have used your '.self::DECLINE_LIMIT.' declines for this month, so you need to accept this request.');
            }

            $offer->update(['state' => 'declined', 'responded_at' => now()]);

            $next = $this->eligibleStaff($request, $offer->offeredBy)->first();

            return $next ? $this->sendTo($request, $next, $offer->offeredBy) : null;
        });
    }

    // Locking the row makes an offer and a Take on the same request queue up instead of both succeeding
    private function lockOpenRequest(ServiceRequest $request): void
    {
        $locked = ServiceRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

        if ($locked->status !== 'open' || $locked->assigned_to !== null || $locked->pendingOffer()->exists()) {
            throw new OfferUnavailableException('This request was taken or offered to someone else first.');
        }
    }

    private function sendTo(ServiceRequest $request, User $to, User $offerer): RequestAssignment
    {
        User::whereKey($to->id)->lockForUpdate()->first();

        // Option A: no declines left, so the offer is accepted on their behalf
        if ($this->declinesThisMonth($to) >= self::DECLINE_LIMIT) {
            $row = $request->assignments()->create([
                'assigned_to' => $to->id,
                'offered_by' => $offerer->id,
                'state' => 'accepted',
                'responded_at' => now(),
            ]);
            $this->assign($request, $to, $offerer);

            return $row;
        }

        return $request->assignments()->create([
            'assigned_to' => $to->id,
            'offered_by' => $offerer->id,
            'state' => 'offered',
        ]);
    }

    // open -> assigned, with the same writes as Take: version, history row, email
    private function assign(ServiceRequest $request, User $staff, User $actor): void
    {
        ServiceRequest::whereKey($request->id)->update([
            'status' => 'assigned',
            'assigned_to' => $staff->id,
            'version' => DB::raw('version + 1'),
            'updated_at' => now(),
        ]);

        $request->history()->create([
            'old_status' => 'open',
            'new_status' => 'assigned',
            'changed_by' => $actor->id,
        ]);

        RequestStatusChanged::dispatch(
            $request->id,
            'CC-'.$request->reference_number,
            'open',
            'assigned',
            $actor->id,
            now()->toIso8601String(),
            $request->requestor->email,
        );
    }
}
