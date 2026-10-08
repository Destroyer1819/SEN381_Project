<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\Fixtures;
use Tests\TestCase;

/** FR-003 - staff search / filter */
class FilterSearchTest extends TestCase
{
    use RefreshDatabase, Fixtures;

    private $staff;
    private $requestor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCategories();
        $this->staff = $this->staff();
        $this->requestor = $this->requestor();
    }

    private function ids($response): array
    {
        $response->assertOk();
        $ids = collect($response->viewData('page')['props']['requests']['data'])->pluck('id')->all();
        sort($ids);

        return $ids;
    }

    // TC-BB-06 | FR-003 | AC: filtering by a single field returns only matching requests
    public function test_filter_by_status_returns_only_matching(): void
    {
        $open = $this->makeRequest($this->requestor);
        $assigned = $this->forceStatus($this->makeRequest($this->requestor), 'assigned', $this->staff);

        $this->assertSame([$open->id], $this->ids($this->actingAs($this->staff)->get('/requests?status=open')));
        $this->assertSame([$assigned->id], $this->ids($this->actingAs($this->staff)->get('/requests?status=assigned')));
        $this->assertSame([], $this->ids($this->actingAs($this->staff)->get('/requests?status=closed')));
    }

    // TC-BB-07 | FR-003 | AC: combined filters return the intersection
    public function test_combined_filters_return_the_intersection(): void
    {
        $a = $this->makeRequest($this->requestor, ['category_id' => 1]);                 // open, facility
        $b = $this->makeRequest($this->requestor, ['category_id' => 2]);                 // open, IT
        $c = $this->forceStatus($this->makeRequest($this->requestor, ['category_id' => 1]), 'assigned', $this->staff); // assigned, facility

        $this->assertSame([$a->id, $c->id], $this->ids($this->actingAs($this->staff)->get('/requests?category_id=1')));
        $this->assertSame([$a->id, $b->id], $this->ids($this->actingAs($this->staff)->get('/requests?status=open')));
        $this->assertSame([$a->id], $this->ids($this->actingAs($this->staff)->get('/requests?status=open&category_id=1')));
    }

    // TC-BB-08 | FR-003 | Boundary value analysis on the date range (both ends inclusive)
    public function test_date_range_boundaries_are_inclusive(): void
    {
        $before = $this->makeRequest($this->requestor);
        $onStart = $this->makeRequest($this->requestor);
        $onEnd = $this->makeRequest($this->requestor);
        $after = $this->makeRequest($this->requestor);
        $this->backdate($before, Carbon::parse('2026-03-09 23:59:59'));
        $this->backdate($onStart, Carbon::parse('2026-03-10 00:00:00'));
        $this->backdate($onEnd, Carbon::parse('2026-03-12 23:59:59'));
        $this->backdate($after, Carbon::parse('2026-03-13 00:00:00'));

        $this->assertSame(
            [$onStart->id, $onEnd->id],
            $this->ids($this->actingAs($this->staff)->get('/requests?from=2026-03-10&to=2026-03-12'))
        );
    }

    // TC-API-14 | FR-003 | Search by reference number and by free text
    public function test_search_by_reference_and_text(): void
    {
        $light = $this->makeRequest($this->requestor, ['description' => 'Flickering ceiling light in the hall']);
        $gate = $this->makeRequest($this->requestor, ['description' => 'Broken gate lock at the back entrance']);

        $this->assertSame([$gate->id], $this->ids($this->actingAs($this->staff)->get('/requests?q=gate')));
        $this->assertSame([$light->id], $this->ids($this->actingAs($this->staff)->get('/requests?q=CC-'.$light->reference_number)));
        $this->assertSame([], $this->ids($this->actingAs($this->staff)->get('/requests?q=nothing-matches-this')));
    }

    public function test_requestor_filters_still_cannot_reach_other_peoples_requests(): void
    {
        $mine = $this->makeRequest($this->requestor);
        $this->makeRequest($this->requestor()); // someone else's

        $this->assertSame([$mine->id], $this->ids($this->actingAs($this->requestor)->get('/requests?status=open')));
    }
}
