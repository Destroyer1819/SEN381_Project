<?php

use App\Models\ServiceRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->staff = User::factory()->create(['role' => 'staff']);
    $this->request = ServiceRequest::factory()->create();
});

test('staff can take an open, unassigned request', function () {
    $this->actingAs($this->staff)
        ->post("/requests/{$this->request->id}/take")
        ->assertRedirect("/requests/{$this->request->id}")
        ->assertSessionHas('success');

    $taken = $this->request->fresh();
    expect($taken->status)->toBe('assigned');
    expect($taken->assigned_to)->toBe($this->staff->id);
    expect($taken->version)->toBe(1);

    $history = $taken->history()->latest('id')->first();
    expect([$history->old_status, $history->new_status, $history->changed_by])->toBe(['open', 'assigned', $this->staff->id]);

    $assignment = $taken->assignments()->first();
    expect([$assignment->assigned_to, $assignment->offered_by, $assignment->state])->toBe([$this->staff->id, $this->staff->id, 'self_assigned']);
});

test('only staff may take a request', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->post("/requests/{$this->request->id}/take")
        ->assertForbidden();

    expect($this->request->fresh()->assigned_to)->toBeNull();
})->with(['requestor', 'management']);

test('a request that is already assigned cannot be taken', function () {
    $other = User::factory()->create(['role' => 'staff']);
    $assigned = ServiceRequest::factory()->assignedTo($other)->create();

    $this->actingAs($this->staff)
        ->post("/requests/{$assigned->id}/take")
        ->assertForbidden();

    expect($assigned->fresh()->assigned_to)->toBe($other->id);
});

test('the detail page offers Take only when it is allowed', function () {
    $this->actingAs($this->staff)
        ->get("/requests/{$this->request->id}")
        ->assertInertia(fn (Assert $page) => $page->where('actions.canTake', true));

    $this->actingAs($this->request->requestor)
        ->get("/requests/{$this->request->id}")
        ->assertInertia(fn (Assert $page) => $page->where('actions.canTake', false));
});