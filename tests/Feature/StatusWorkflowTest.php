<?php

namespace Tests\Feature;

use App\Models\RequestAssignment;
use App\Models\RequestComment;
use App\Models\RequestStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\Fixtures;
use Tests\TestCase;

/** FR-004 (assignment) and FR-005 (controlled status workflow) */
class StatusWorkflowTest extends TestCase
{
    use RefreshDatabase, Fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCategories();
    }

    private function status(int $id, string $to, int $version, ?string $note = null): array
    {
        return array_filter(['status' => $to, 'version' => $version, 'note' => $note], fn ($v) => $v !== null);
    }

    // TC-API-08 | FR-004 | AC: assignment updates owner and is visible in history
    public function test_staff_can_take_an_open_request(): void
    {
        $staff = $this->staff();
        $request = $this->makeRequest($this->requestor());

        $this->actingAs($staff)->post("/requests/{$request->id}/take")->assertSessionHasNoErrors();

        $request->refresh();
        $this->assertSame('assigned', $request->status);
        $this->assertSame($staff->id, $request->assigned_to);
        $this->assertSame(1, $request->version);
        $this->assertDatabaseHas('request_status_history', ['request_id' => $request->id, 'old_status' => 'open', 'new_status' => 'assigned', 'changed_by' => $staff->id]);
        $this->assertDatabaseHas('request_assignments', ['request_id' => $request->id, 'state' => 'self_assigned', 'assigned_to' => $staff->id, 'offered_by' => $staff->id]);
    }

    // TC-API-09 | FR-004 | Negative: a request that is already assigned cannot be taken again
    public function test_cannot_take_a_request_that_is_not_open(): void
    {
        $first = $this->staff();
        $second = $this->staff();
        $request = $this->makeRequest($this->requestor());

        $this->actingAs($first)->post("/requests/{$request->id}/take");
        $this->actingAs($second)->post("/requests/{$request->id}/take")->assertSessionHasErrors('request');

        $this->assertSame($first->id, $request->fresh()->assigned_to);
    }

    // TC-API-10 | FR-005 | State transition: open -> assigned -> in_progress -> resolved -> closed, with audit trail
    public function test_full_lifecycle_records_every_step(): void
    {
        $staff = $this->staff();
        $request = $this->makeRequest($this->requestor());

        $this->actingAs($staff)->post("/requests/{$request->id}/take");
        $this->actingAs($staff)->post("/requests/{$request->id}/status", $this->status($request->id, 'in_progress', 1))->assertSessionHasNoErrors();
        $this->actingAs($staff)->post("/requests/{$request->id}/status", $this->status($request->id, 'resolved', 2, 'Replaced the faulty bulb.'))->assertSessionHasNoErrors();

        $request->refresh();
        $this->assertSame('resolved', $request->status);
        $this->assertNotNull($request->resolved_at);
        $this->assertDatabaseHas('request_comments', ['request_id' => $request->id, 'is_resolution' => true, 'comment' => 'Replaced the faulty bulb.']);

        $this->actingAs($staff)->post("/requests/{$request->id}/status", $this->status($request->id, 'closed', 3))->assertSessionHasNoErrors();

        $this->assertSame('closed', $request->fresh()->status);
        $this->assertSame(
            [[null, 'open'], ['open', 'assigned'], ['assigned', 'in_progress'], ['in_progress', 'resolved'], ['resolved', 'closed']],
            RequestStatusHistory::where('request_id', $request->id)->orderBy('id')->get()->map(fn ($h) => [$h->old_status, $h->new_status])->all()
        );
    }

    // TC-BB-04 | FR-005 | Decision table of invalid moves: each must be rejected and leave the request unchanged
    #[DataProvider('invalidMoves')]
    public function test_invalid_transitions_are_rejected(string $from, string $to): void
    {
        $manager = $this->manager(); // management may act on any request, so only the workflow rule is under test
        $request = $this->forceStatus($this->makeRequest($this->requestor()), $from, $this->staff());

        $this->actingAs($manager)
            ->post("/requests/{$request->id}/status", $this->status($request->id, $to, $request->version, 'note'))
            ->assertSessionHasErrors('status');

        $this->assertSame($from, $request->fresh()->status);
    }

    public static function invalidMoves(): array
    {
        return [
            'closed -> assigned' => ['closed', 'assigned'],
            'closed -> in_progress' => ['closed', 'in_progress'],
            'open -> resolved' => ['open', 'resolved'],
            'open -> in_progress' => ['open', 'in_progress'],
            'assigned -> resolved' => ['assigned', 'resolved'],
            'resolved -> assigned' => ['resolved', 'assigned'],
        ];
    }

    // TC-BB-05 | FR-005 | Resolving requires a resolution note (empty / whitespace / provided)
    public function test_resolution_note_is_required_to_resolve(): void
    {
        $staff = $this->staff();
        $request = $this->forceStatus($this->makeRequest($this->requestor()), 'in_progress', $staff);

        $this->actingAs($staff)->post("/requests/{$request->id}/status", $this->status($request->id, 'resolved', $request->version))->assertSessionHasErrors('note');
        $this->actingAs($staff)->post("/requests/{$request->id}/status", $this->status($request->id, 'resolved', $request->version, '   '))->assertSessionHasErrors('note');
        $this->assertSame('in_progress', $request->fresh()->status);

        $this->actingAs($staff)->post("/requests/{$request->id}/status", $this->status($request->id, 'resolved', $request->version, 'Done.'))->assertSessionHasNoErrors();
        $this->assertSame('resolved', $request->fresh()->status);
    }

    // TC-API-11 | FR-005 | Concurrency: a stale version number (someone else changed the request) is rejected
    public function test_stale_version_is_rejected(): void
    {
        $staff = $this->staff();
        $request = $this->makeRequest($this->requestor());
        $this->actingAs($staff)->post("/requests/{$request->id}/take");           // version 0 -> 1
        $this->actingAs($staff)->post("/requests/{$request->id}/status", $this->status($request->id, 'in_progress', 1)); // 1 -> 2

        // A second browser tab still holds version 1
        $this->actingAs($staff)->post("/requests/{$request->id}/status", $this->status($request->id, 'resolved', 1, 'Fixed'))
            ->assertSessionHasErrors('request');

        $this->assertSame('in_progress', $request->fresh()->status);
    }

    // TC-API-12 | FR-005 / FR-008 | Only the assigned staff member (or management) may change the status
    public function test_only_assignee_or_management_can_change_status(): void
    {
        $owner = $this->staff();
        $other = $this->staff();
        $request = $this->forceStatus($this->makeRequest($this->requestor()), 'assigned', $owner);

        $this->actingAs($other)->post("/requests/{$request->id}/status", $this->status($request->id, 'in_progress', $request->version))->assertForbidden();
        $this->assertSame('assigned', $request->fresh()->status);

        $this->actingAs($this->manager())->post("/requests/{$request->id}/status", $this->status($request->id, 'in_progress', $request->version))->assertSessionHasNoErrors();
        $this->assertSame('in_progress', $request->fresh()->status);
    }

    // TC-API-13 | FR-004 | Offer to another staff member: accept assigns, decline leaves it open
    public function test_offer_can_be_accepted_or_declined(): void
    {
        $offerer = $this->staff();
        $target = $this->staff();
        $request = $this->makeRequest($this->requestor());

        $this->actingAs($offerer)->post("/requests/{$request->id}/offer", ['assigned_to' => $target->id])->assertSessionHasNoErrors();
        $offer = RequestAssignment::where('request_id', $request->id)->firstOrFail();
        $this->assertSame('offered', $offer->state);
        $this->assertSame('open', $request->fresh()->status);

        // someone other than the target cannot answer it
        $this->actingAs($offerer)->post("/assignments/{$offer->id}/respond", ['decision' => 'accept'])->assertForbidden();

        $this->actingAs($target)->post("/assignments/{$offer->id}/respond", ['decision' => 'decline']);
        $this->assertSame('declined', $offer->fresh()->state);
        $this->assertSame('open', $request->fresh()->status);

        $this->actingAs($offerer)->post("/requests/{$request->id}/offer", ['assigned_to' => $target->id]);
        $second = RequestAssignment::where('request_id', $request->id)->where('state', 'offered')->firstOrFail();
        $this->actingAs($target)->post("/assignments/{$second->id}/respond", ['decision' => 'accept']);

        $request->refresh();
        $this->assertSame('assigned', $request->status);
        $this->assertSame($target->id, $request->assigned_to);
        $this->assertSame('accepted', $second->fresh()->state);
    }

    public function test_comments_can_be_added_by_the_requestor_but_not_by_a_stranger(): void
    {
        $alice = $this->requestor();
        $request = $this->makeRequest($alice);

        $this->actingAs($alice)->post("/requests/{$request->id}/comments", ['body' => 'Any update?'])->assertSessionHasNoErrors();
        $this->assertSame(1, RequestComment::where('request_id', $request->id)->count());

        $this->actingAs($this->requestor())->post("/requests/{$request->id}/comments", ['body' => 'Hi'])->assertForbidden();
        $this->assertSame(1, RequestComment::where('request_id', $request->id)->count());
    }
}
