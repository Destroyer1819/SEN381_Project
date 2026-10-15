<?php

use App\Events\RequestStatusChanged;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->manager = User::factory()->create(['role' => 'management']);
    $this->alice = User::factory()->create(['role' => 'staff']);
    $this->bob = User::factory()->create(['role' => 'staff']);
    $this->request = ServiceRequest::factory()->create(); // open and unassigned
});

// Test data: $count declines for $staff, dated "now". The offerer is management so they never
// show up as a "next person" candidate.
function giveDeclines(User $staff, int $count): void
{
    $offerer = User::factory()->create(['role' => 'management']);

    foreach (range(1, $count) as $_) {
        ServiceRequest::factory()->create()->assignments()->create([
            'assigned_to' => $staff->id,
            'offered_by' => $offerer->id,
            'state' => 'declined',
            'responded_at' => now(),
        ]);
    }
}

function offerTo(ServiceRequest $request, User $from, User $to)
{
    return test()->actingAs($from)->post("/requests/{$request->id}/offer", ['assigned_to' => $to->id]);
}

function answer(ServiceRequest $request, User $staff, string $decision)
{
    return test()->actingAs($staff)->post("/assignments/{$request->fresh()->pendingOffer->id}/respond", ['decision' => $decision]);
}

// FR-015: an offer waits for an answer; the request stays open and unassigned
test('an offer waits for the staff member to answer', function () {
    offerTo($this->request, $this->manager, $this->alice)
        ->assertSessionHas('success', "Offered to {$this->alice->name}.");

    $offer = $this->request->pendingOffer()->sole();
    expect([$offer->assigned_to, $offer->offered_by])->toBe([$this->alice->id, $this->manager->id]);
    expect($this->request->fresh()->assigned_to)->toBeNull();
});

// D-018 boundary values: 2 declines this month still gets a normal offer, 3 is assigned automatically
test('the decline limit decides whether an offer waits or is accepted automatically', function (int $declines, string $state) {
    giveDeclines($this->alice, $declines);

    offerTo($this->request, $this->manager, $this->alice);

    $row = $this->request->assignments()->sole();
    expect($row->state)->toBe($state);
    expect($this->request->fresh()->assigned_to)->toBe($state === 'accepted' ? $this->alice->id : null);
})->with([
    '2 declines' => [2, 'offered'],
    '3 declines' => [3, 'accepted'],
]);

// D-018: an automatic accept writes the same history row and email as a normal assignment
test('an automatic accept assigns the request and tells the requester', function () {
    Event::fake([RequestStatusChanged::class]);
    giveDeclines($this->alice, 3);

    offerTo($this->request, $this->manager, $this->alice);

    $assigned = $this->request->fresh();
    expect([$assigned->status, $assigned->version])->toBe(['assigned', 1]);
    $history = $assigned->history()->sole();
    expect([$history->old_status, $history->new_status, $history->changed_by])->toBe(['open', 'assigned', $this->manager->id]);
    Event::assertDispatched(RequestStatusChanged::class, fn (RequestStatusChanged $e) => $e->requestId === $this->request->id);
});

// D-018 boundary: the count resets on the 1st of the month
test('declines from last month do not count', function () {
    $this->travelTo(now()->setDate(2026, 10, 31)->setTime(16, 0));
    giveDeclines($this->alice, 3);

    $this->travelTo(now()->setDate(2026, 11, 1)->setTime(9, 0));
    offerTo($this->request, $this->manager, $this->alice);

    expect($this->request->assignments()->sole()->state)->toBe('offered');
});

// FR-015: accepting assigns the request to the person who accepted
test('accepting an offer assigns the request', function () {
    Event::fake([RequestStatusChanged::class]);
    offerTo($this->request, $this->manager, $this->alice);

    answer($this->request, $this->alice, 'accept')->assertSessionHas('success');

    $assigned = $this->request->fresh();
    expect([$assigned->status, $assigned->assigned_to])->toBe(['assigned', $this->alice->id]);
    expect($assigned->assignments()->sole()->state)->toBe('accepted');
    $history = $assigned->history()->sole();
    expect([$history->old_status, $history->new_status, $history->changed_by])->toBe(['open', 'assigned', $this->alice->id]);
    Event::assertDispatched(RequestStatusChanged::class);
});

// D-018: a decline goes to whoever has the fewest open requests, not simply the next id
test('a decline passes the offer to the staff member with the fewest open requests', function () {
    $carol = User::factory()->create(['role' => 'staff']); // higher id than bob
    ServiceRequest::factory()->assignedTo($this->bob)->create(); // bob is busier than carol

    offerTo($this->request, $this->manager, $this->alice);
    answer($this->request, $this->alice, 'decline')->assertSessionHas('success');

    $next = $this->request->pendingOffer()->sole();
    expect([$next->assigned_to, $next->offered_by])->toBe([$carol->id, $this->manager->id]);
});

// D-018: when everyone eligible has declined, the request goes back to the open pool
test('when nobody is left the request goes back to the open pool', function () {
    offerTo($this->request, $this->manager, $this->alice);
    answer($this->request, $this->alice, 'decline');
    answer($this->request, $this->bob, 'decline');

    expect($this->request->pendingOffer()->exists())->toBeFalse();
    expect($this->request->fresh()->assigned_to)->toBeNull();
    $this->actingAs($this->bob)->post("/requests/{$this->request->id}/take")->assertSessionHas('success');
});

// D-018 negative: a decline that would be the 4th this month is refused and the offer stays open
test('a staff member with no declines left cannot decline', function () {
    offerTo($this->request, $this->manager, $this->alice);
    giveDeclines($this->alice, 3);

    answer($this->request, $this->alice, 'decline')
        ->assertSessionHasErrors(['request' => 'You have used your 3 declines for this month, so you need to accept this request.']);

    expect($this->request->pendingOffer()->sole()->assigned_to)->toBe($this->alice->id);
});

// D-018 negative: Take is blocked while an offer is waiting
test('a request with a pending offer cannot be taken', function () {
    offerTo($this->request, $this->manager, $this->alice);

    $this->actingAs($this->bob)
        ->post("/requests/{$this->request->id}/take")
        ->assertForbidden();

    expect($this->request->fresh()->assigned_to)->toBeNull();
});

// FR-015 negative: only the person the offer went to can answer it
test('only the person offered can answer the offer', function () {
    offerTo($this->request, $this->manager, $this->alice);

    answer($this->request, $this->bob, 'accept')->assertForbidden();

    expect($this->request->pendingOffer()->sole()->state)->toBe('offered');
});

// FR-015 negative: requestors cannot hand requests out
test('a requestor cannot make an offer', function () {
    offerTo($this->request, $this->request->requestor, $this->alice)->assertForbidden();

    expect($this->request->assignments()->count())->toBe(0);
});

// Validation: a hand-edited form cannot offer to someone outside the eligible list
test('an offer to someone who is not eligible is rejected', function (string $who) {
    [$from, $to] = match ($who) {
        'a requestor' => [$this->manager, $this->request->requestor],
        'staff offering to themselves' => [$this->alice, $this->alice],
    };

    offerTo($this->request, $from, $to)
        ->assertSessionHasErrors(['assigned_to' => 'That staff member cannot be offered this request.']);

    expect($this->request->assignments()->count())->toBe(0);
})->with(['a requestor', 'staff offering to themselves']);

// The detail page: offer controls for anyone who may offer, Accept / Decline only for the offeree
test('the detail page shows offer controls to the right people', function () {
    $this->actingAs($this->manager)
        ->get("/requests/{$this->request->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('actions.canOffer', true)
            ->where('actions.staff', [
                ['id' => $this->alice->id, 'name' => $this->alice->name],
                ['id' => $this->bob->id, 'name' => $this->bob->name],
            ]));

    offerTo($this->request, $this->manager, $this->alice);
    $offerId = $this->request->fresh()->pendingOffer->id;

    $this->actingAs($this->alice)
        ->get("/requests/{$this->request->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('actions.pendingOfferId', $offerId)
            ->where('actions.canOffer', false)
            ->where('actions.canTake', false));

    $this->actingAs($this->bob)
        ->get("/requests/{$this->request->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('actions.pendingOfferId', null)
            ->where('actions.pendingOfferTo', $this->alice->name));
});

// While an offer waits, staff and management see who it is waiting on; the offeree and the requestor do not
test('the detail page says who a pending offer is waiting on', function () {
    offerTo($this->request, $this->manager, $this->alice);

    foreach ([[$this->manager, $this->alice->name], [$this->bob, $this->alice->name], [$this->alice, null], [$this->request->requestor, null]] as [$viewer, $expected]) {
        $this->actingAs($viewer)
            ->get("/requests/{$this->request->id}")
            ->assertInertia(fn (Assert $page) => $page->where('actions.pendingOfferTo', $expected));
    }
});
