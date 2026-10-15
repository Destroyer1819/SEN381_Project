<?php

use App\Models\ServiceRequest;
use App\Models\User;

beforeEach(function () {
    $this->staff = User::factory()->create(['role' => 'staff']);
    $this->request = ServiceRequest::factory()->assignedTo($this->staff)->create();
});

// FR-017, FR-019: a legal move updates the request and writes one history row
test('the assigned staff member can start work on a request', function () {
    $this->actingAs($this->staff)
        ->post("/requests/{$this->request->id}/status", ['status' => 'in_progress', 'version' => 0])
        ->assertRedirect("/requests/{$this->request->id}")
        ->assertSessionHas('success');

    $changed = $this->request->fresh();
    expect($changed->status)->toBe('in_progress');
    expect($changed->version)->toBe(1);

    $history = $changed->history()->latest('id')->first();
    expect([$history->old_status, $history->new_status, $history->changed_by])
        ->toBe(['assigned', 'in_progress', $this->staff->id]);
});

// FR-017 state transition, negative: assigned -> closed is not in the transition table
test('a transition that is not allowed is rejected and nothing is written', function () {
    $this->actingAs($this->staff)
        ->post("/requests/{$this->request->id}/status", ['status' => 'closed', 'version' => 0])
        ->assertSessionHasErrors('status');

    expect($this->request->fresh()->status)->toBe('assigned');
    expect($this->request->history()->count())->toBe(0);
});

// R-008, D-008: someone else changed the request after the page loaded
test('a stale version is rejected and nothing is written', function () {
    $this->actingAs($this->staff)
        ->post("/requests/{$this->request->id}/status", ['status' => 'in_progress', 'version' => 5])
        ->assertSessionHasErrors('request');

    expect($this->request->fresh()->version)->toBe(0);
    expect($this->request->history()->count())->toBe(0);
});