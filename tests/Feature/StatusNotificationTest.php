<?php

use App\Events\RequestStatusChanged;
use App\Listeners\NotifyRequesterOfStatusChange;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->staff = User::factory()->create(['role' => 'staff']);
    $this->request = ServiceRequest::factory()->assignedTo($this->staff)->create();
});

// D-009, 13.6.4: a successful change raises the event with the agreed payload
test('a status change raises RequestStatusChanged', function () {
    Event::fake([RequestStatusChanged::class]);

    $this->actingAs($this->staff)
        ->post("/requests/{$this->request->id}/status", ['status' => 'in_progress', 'version' => 0]);

    Event::assertDispatched(RequestStatusChanged::class, fn (RequestStatusChanged $e) => $e->requestId === $this->request->id
        && [$e->fromStatus, $e->toStatus] === ['assigned', 'in_progress']
        && $e->actorId === $this->staff->id
        && $e->requesterEmail === $this->request->requestor->email);
});

// D-008, D-011: a rejected change must not tell the requester anything happened
test('a stale change raises no event', function () {
    Event::fake([RequestStatusChanged::class]);

    $this->actingAs($this->staff)
        ->post("/requests/{$this->request->id}/status", ['status' => 'in_progress', 'version' => 5]);

    Event::assertNotDispatched(RequestStatusChanged::class);
});

// FR-006: requesters also hear when staff take their request
test('taking a request raises RequestStatusChanged', function () {
    Event::fake([RequestStatusChanged::class]);
    $open = ServiceRequest::factory()->create();

    $this->actingAs($this->staff)->post("/requests/{$open->id}/take");

    Event::assertDispatched(RequestStatusChanged::class, fn (RequestStatusChanged $e) => $e->requestId === $open->id
        && [$e->fromStatus, $e->toStatus] === ['open', 'assigned']
        && $e->actorId === $this->staff->id
        && $e->requesterEmail === $open->requestor->email);
});

// FR-006 negative: a refused take (request already assigned) must not email the requester
test('a refused take raises no event', function () {
    Event::fake([RequestStatusChanged::class]);
    $other = User::factory()->create(['role' => 'staff']);
    $taken = ServiceRequest::factory()->assignedTo($other)->create();

    $this->actingAs($this->staff)->post("/requests/{$taken->id}/take");

    Event::assertNotDispatched(RequestStatusChanged::class);
});

// D-011: the listener is wired up and runs on the queue
test('the listener is registered and queued', function () {
    Event::fake();

    Event::assertListening(RequestStatusChanged::class, NotifyRequesterOfStatusChange::class);
    expect(new NotifyRequesterOfStatusChange)->toBeInstanceOf(ShouldQueue::class);
});

// FR-006: the requester is emailed at the address carried by the event
test('the listener emails the requester', function () {
    Notification::fake();
    $event = new RequestStatusChanged(1, 'CC-2026000001', 'assigned', 'in_progress', $this->staff->id, now()->toIso8601String(), 'requester@example.com');

    (new NotifyRequesterOfStatusChange)->handle($event);

    Notification::assertSentOnDemand(RequestStatusChangedMail::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'requester@example.com');
});
