<?php

use App\Models\ServiceRequest;
use App\Models\User;

function listIdsFor(User $user, string $query = ''): array
{
    $response = test()->actingAs($user)->get("/requests{$query}")->assertOk();

    return collect($response->viewData('page')['props']['requests']['data'])->pluck('id')->sort()->values()->all();
}

// FR-021: a resolved request leaves the staff member's active list but stays in the system
test('"Active only" hides resolved and closed requests', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $open = ServiceRequest::factory()->create();
    $working = ServiceRequest::factory()->assignedTo($staff)->create(['status' => 'in_progress']);
    $resolved = ServiceRequest::factory()->assignedTo($staff)->create(['status' => 'resolved', 'resolved_at' => now()]);
    $closed = ServiceRequest::factory()->assignedTo($staff)->create(['status' => 'closed', 'resolved_at' => now()]);

    expect(listIdsFor($staff, '?active=1'))->toBe([$open->id, $working->id]);
    expect(listIdsFor($staff))->toBe([$open->id, $working->id, $resolved->id, $closed->id]);
});

// FR-023: management can list exactly the overdue requests (not resolved or closed, submitted over 7 days ago)
test('"Overdue only" lists only overdue requests', function () {
    $manager = User::factory()->create(['role' => 'management']);
    $days = ServiceRequest::OVERDUE_DAYS;
    $overdue = ServiceRequest::factory()->create(['created_at' => now()->subDays($days + 1)]);
    ServiceRequest::factory()->create(['created_at' => now()->subDays($days - 1)]); // not overdue yet
    ServiceRequest::factory()->create(['status' => 'closed', 'resolved_at' => now(), 'created_at' => now()->subDays($days + 5)]); // finished

    expect(listIdsFor($manager, '?overdue=1'))->toBe([$overdue->id]);
});

// The filters never widen what a role may see (FR-011)
test('the new filters keep each role to its own requests', function () {
    $requestor = User::factory()->create();
    $mine = ServiceRequest::factory()->create(['requestor_id' => $requestor->id, 'created_at' => now()->subDays(30)]);
    ServiceRequest::factory()->create(['created_at' => now()->subDays(30)]); // someone else's, also overdue

    expect(listIdsFor($requestor, '?active=1&overdue=1'))->toBe([$mine->id]);
});

// The page gets the filters back so the checkboxes keep their state
test('the active and overdue filters are echoed back to the page', function () {
    $response = $this->actingAs(User::factory()->create(['role' => 'staff']))->get('/requests?active=1&overdue=1');

    expect($response->viewData('page')['props']['filters'])->toMatchArray(['active' => true, 'overdue' => true]);
});
