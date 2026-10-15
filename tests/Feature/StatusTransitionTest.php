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

// FR-018 decision table: only the staff member assigned to the request may change its status
test('only the assigned staff member may change the status', function (string $who) {
    $user = match ($who) {
        'owner' => $this->request->requestor,
        'other staff' => User::factory()->create(['role' => 'staff']),
        'management' => User::factory()->create(['role' => 'management']),
    };

    $this->actingAs($user)
        ->post("/requests/{$this->request->id}/status", ['status' => 'in_progress', 'version' => 0])
        ->assertForbidden();

    expect($this->request->fresh()->status)->toBe('assigned');
    expect($this->request->history()->count())->toBe(0);
})->with(['owner', 'other staff', 'management']);

// FR-020, FR-021: a request cannot be resolved without saying how
test('resolving without a note is rejected', function () {
    $working = ServiceRequest::factory()->assignedTo($this->staff)->create(['status' => 'in_progress']);

    $this->actingAs($this->staff)
        ->post("/requests/{$working->id}/status", ['status' => 'resolved', 'version' => 0, 'note' => ''])
        ->assertSessionHasErrors(['note' => 'Add a resolution note before marking the request as resolved.']);

    $unchanged = $working->fresh();
    expect($unchanged->status)->toBe('in_progress');
    expect($unchanged->resolved_at)->toBeNull();
    expect($unchanged->comments()->count())->toBe(0);
});

// FR-020, FR-021: the note is kept as a resolution comment and the resolve time is recorded
test('resolving with a note saves it and records when the request was resolved', function () {
    $working = ServiceRequest::factory()->assignedTo($this->staff)->create(['status' => 'in_progress']);

    $this->actingAs($this->staff)
        ->post("/requests/{$working->id}/status", ['status' => 'resolved', 'version' => 0, 'note' => 'Replaced the power cable.'])
        ->assertSessionHasNoErrors();

    $resolved = $working->fresh();
    expect($resolved->status)->toBe('resolved');
    expect($resolved->resolved_at)->not->toBeNull();

    $note = $resolved->comments()->sole();
    expect([$note->comment, $note->is_resolution, $note->author_id])
        ->toBe(['Replaced the power cable.', true, $this->staff->id]);
});

// FR-024 data accuracy: a re-opened request must not still count as resolved
test('re-opening a resolved request clears its resolved time', function () {
    $resolved = ServiceRequest::factory()->assignedTo($this->staff)->create(['status' => 'resolved', 'resolved_at' => now()]);

    $this->actingAs($this->staff)
        ->post("/requests/{$resolved->id}/status", ['status' => 'in_progress', 'version' => 0])
        ->assertSessionHasNoErrors();

    $reopened = $resolved->fresh();
    expect($reopened->status)->toBe('in_progress');
    expect($reopened->resolved_at)->toBeNull();
});

// R-009 mitigation, FR-019: every status change leaves exactly one history row, in order
test('every status change writes exactly one history row', function () {
    $steps = [
        ['status' => 'in_progress'],
        ['status' => 'resolved', 'note' => 'Replaced the power cable.'],
        ['status' => 'in_progress'],
        ['status' => 'resolved', 'note' => 'Replaced the wall socket as well.'],
        ['status' => 'closed'],
    ];

    // The version goes up by one with each change, so step N is sent with version N
    foreach ($steps as $version => $step) {
        $this->actingAs($this->staff)
            ->post("/requests/{$this->request->id}/status", [...$step, 'version' => $version])
            ->assertSessionHasNoErrors();
    }

    $closed = $this->request->fresh();
    expect($closed->status)->toBe('closed');
    expect($closed->version)->toBe(count($steps));
    expect($closed->history()->orderBy('id')->get()->map(fn ($h) => "{$h->old_status} > {$h->new_status}")->all())
        ->toBe([
            'assigned > in_progress',
            'in_progress > resolved',
            'resolved > in_progress',
            'in_progress > resolved',
            'resolved > closed',
        ]);
});
