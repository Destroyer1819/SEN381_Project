<?php

namespace App\Http\Controllers;

use App\Exceptions\StaleRequestException;
use App\Models\ServiceRequest;
use App\Services\RequestStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RequestStatusController extends Controller
{
    // Laravel's container builds RequestStatusService and passes it in.
    public function store(Request $request, ServiceRequest $serviceRequest, RequestStatusService $statuses): RedirectResponse
    {
        Gate::authorize('changeStatus', $serviceRequest);

        $data = $request->validate([
            'status' => ['required', Rule::in(ServiceRequest::STATUSES)],
            'version' => ['required', 'integer'],
            'note' => ['nullable', 'required_if:status,resolved', 'string', 'max:2000'],
        ], [
            'note.required_if' => 'Add a resolution note before marking the request as resolved.',
        ]);

        try {
            $statuses->transition($serviceRequest, $data['status'], (int) $data['version'], $request->user(), $data['note'] ?? null);
        } catch (StaleRequestException) {
            return to_route('requests.show', $serviceRequest)
                ->withErrors(['request' => 'This request changed since you opened it. The latest version is shown below.']);
        }

        return to_route('requests.show', $serviceRequest)->with('success', 'Status updated.');
    }
}
