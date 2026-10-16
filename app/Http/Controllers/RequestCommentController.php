<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RequestCommentController extends Controller
{
    public function store(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        Gate::authorize('comment', $serviceRequest);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => 'Enter a comment.',
        ]);

        $serviceRequest->comments()->create([
            'author_id' => $request->user()->id,
            'comment' => $data['body'],
            'is_resolution' => false,
        ]);

        $serviceRequest->touch();

        return to_route('requests.show', $serviceRequest)->with('success', 'Comment added.');
    }
}
