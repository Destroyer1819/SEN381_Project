<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RequestAssignmentController extends Controller
{
    public function take(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        Gate::authorize('take', $serviceRequest);

        $user = $request->user();

        $taken = DB::transaction(function () use ($serviceRequest, $user) {
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

            return true;
        });

        if (! $taken) {
            return to_route('requests.show', $serviceRequest)
                ->withErrors(['request' => 'Someone else took this request first.']);
        }

        return to_route('requests.show', $serviceRequest)->with('success', 'You have taken this request.');
    }
}
