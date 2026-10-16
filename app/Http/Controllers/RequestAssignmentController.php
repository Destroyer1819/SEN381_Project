<?php

namespace App\Http\Controllers;

use App\Events\RequestStatusChanged;
use App\Exceptions\OfferUnavailableException;
use App\Models\RequestAssignment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\OfferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RequestAssignmentController extends Controller
{
    public function take(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        Gate::authorize('take', $serviceRequest);

        $user = $request->user();

        $taken = DB::transaction(function () use ($serviceRequest, $user) {
            // Waits for any offer being made on this request, then refuses if one is pending (D-018)
            $locked = ServiceRequest::whereKey($serviceRequest->id)->lockForUpdate()->first();
            if ($locked->pendingOffer()->exists()) {
                return false;
            }

            // Only succeeds if nobody took it since the page loaded
            $updated = ServiceRequest::whereKey($serviceRequest->id)
                ->where('status', 'open')
                ->whereNull('assigned_to')
                ->update([
                    'status' => 'assigned',
                    'assigned_to' => $user->id,
                    'version' => DB::raw('version + 1'),
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                return false;
            }

            $serviceRequest->history()->create([
                'old_status' => 'open',
                'new_status' => 'assigned',
                'changed_by' => $user->id,
            ]);

            $serviceRequest->assignments()->create([
                'assigned_to' => $user->id,
                'offered_by' => $user->id,
                'state' => 'self_assigned',
            ]);

            // Held until the transaction commits, so a failed take never emails the requester
            RequestStatusChanged::dispatch(
                $serviceRequest->id,
                'CC-'.$serviceRequest->reference_number,
                'open',
                'assigned',
                $user->id,
                now()->toIso8601String(),
                $serviceRequest->requestor->email,
            );

            return true;
        });

        if (! $taken) {
            return to_route('requests.show', $serviceRequest)
                ->withErrors(['request' => 'Someone else took this request first.']);
        }

        return to_route('requests.show', $serviceRequest)->with('success', 'You have taken this request.');
    }

    public function offer(Request $request, ServiceRequest $serviceRequest, OfferService $offers): RedirectResponse
    {
        Gate::authorize('offer', $serviceRequest);

        // Only people the service would accept, so a hand-edited form cannot offer to a requestor or a decliner
        $eligible = $offers->eligibleStaff($serviceRequest, $request->user())->pluck('id')->all();

        $data = $request->validate([
            'assigned_to' => ['required', 'integer', Rule::in($eligible)],
        ], [
            'assigned_to.required' => 'Choose a staff member to offer this request to.',
            'assigned_to.in' => 'That staff member cannot be offered this request.',
        ]);

        $to = User::findOrFail($data['assigned_to']);

        try {
            $row = $offers->offer($serviceRequest, $to, $request->user());
        } catch (OfferUnavailableException $e) {
            return to_route('requests.show', $serviceRequest)->withErrors(['request' => $e->getMessage()]);
        }

        $message = $row->state === 'accepted'
            ? "{$to->name} has used all ".OfferService::DECLINE_LIMIT.' declines this month, so the request was assigned to them.'
            : "Offered to {$to->name}.";

        return to_route('requests.show', $serviceRequest)->with('success', $message);
    }

    public function respond(Request $request, RequestAssignment $assignment, OfferService $offers): RedirectResponse
    {
        Gate::authorize('respond', $assignment);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['accept', 'decline'])],
        ]);

        try {
            $offers->respond($assignment, $request->user(), $data['decision']);
        } catch (OfferUnavailableException $e) {
            return to_route('requests.show', $assignment->request_id)->withErrors(['request' => $e->getMessage()]);
        }

        $message = $data['decision'] === 'accept' ? 'You have accepted this request.' : 'You declined this request.';

        return to_route('requests.show', $assignment->request_id)->with('success', $message);
    }
}
