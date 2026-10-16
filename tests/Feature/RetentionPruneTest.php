<?php

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// A closed request with a history row, an assignment and a comment, last updated $updatedAt
function requestWithRecords($updatedAt): ServiceRequest
{
    $staff = User::factory()->create(['role' => 'staff']);
    $request = ServiceRequest::factory()->assignedTo($staff)->create(['status' => 'closed', 'resolved_at' => now()]);

    $request->history()->create(['old_status' => null, 'new_status' => 'open', 'changed_by' => $request->requestor_id]);
    $request->assignments()->create(['assigned_to' => $staff->id, 'offered_by' => $staff->id, 'state' => 'self_assigned']);
    $request->comments()->create(['author_id' => $staff->id, 'comment' => 'Fixed.', 'is_resolution' => true]);

    // Set last, because adding a comment touches updated_at
    DB::table('service_requests')->where('id', $request->id)->update(['updated_at' => $updatedAt]);

    return $request;
}

function recordCounts(ServiceRequest $request): array
{
    return [
        'request' => ServiceRequest::whereKey($request->id)->count(),
        'history' => DB::table('request_status_history')->where('request_id', $request->id)->count(),
        'assignments' => DB::table('request_assignments')->where('request_id', $request->id)->count(),
        'comments' => DB::table('request_comments')->where('request_id', $request->id)->count(),
    ];
}

// FR-008, FR-016, FR-019, FR-020 (CR-012): records are kept 5 years after the last update, then purged together.
// Boundary values: one day past 5 years is purged, one day short of 5 years is kept.
test('the retention purge removes requests older than 5 years with all their records', function () {
    $expired = requestWithRecords(now()->subYears(ServiceRequest::Retention_Years)->subDay());
    $kept = requestWithRecords(now()->subYears(ServiceRequest::Retention_Years)->addDay());

    $this->artisan('model:prune', ['--model' => [ServiceRequest::class]])->assertSuccessful();

    expect(recordCounts($expired))->toBe(['request' => 0, 'history' => 0, 'assignments' => 0, 'comments' => 0]);
    expect(recordCounts($kept))->toBe(['request' => 1, 'history' => 1, 'assignments' => 1, 'comments' => 1]);
});

// The purge is scheduled, so it actually runs without anyone remembering to start it
test('the retention purge is scheduled daily', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('model:prune')
        ->assertSuccessful();
});
