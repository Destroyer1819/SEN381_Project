<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\Fixtures;
use Tests\TestCase;

/** FR-007 - management dashboard */
class DashboardTest extends TestCase
{
    use RefreshDatabase, Fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCategories();
    }

    // TC-API-15 | FR-007 | AC: dashboard counts match the underlying request data
    public function test_counts_match_the_data(): void
    {
        $staff = $this->staff();
        $who = $this->requestor();
        $this->makeRequest($who);
        $this->makeRequest($who);
        $this->forceStatus($this->makeRequest($who), 'in_progress', $staff);
        $this->forceStatus($this->makeRequest($who), 'resolved', $staff);
        $this->forceStatus($this->makeRequest($who), 'closed', $staff);

        $this->actingAs($this->manager())->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('stats.total', 5)
            ->where('stats.by_status.open', 2)
            ->where('stats.by_status.in_progress', 1)
            ->where('stats.by_status.resolved', 1)
            ->where('stats.by_status.closed', 1)
            ->where('stats.by_status.assigned', 0)
            ->where('stats.overdue', 0));
    }

    // TC-API-16 | FR-007 | Overdue = not resolved/closed and older than the configured days; category filter narrows counts
    public function test_overdue_and_category_filter(): void
    {
        $staff = $this->staff();
        $who = $this->requestor();

        $oldOpen = $this->makeRequest($who, ['category_id' => 1]);
        $this->backdate($oldOpen, now()->subDays(5));
        $oldResolved = $this->forceStatus($this->makeRequest($who, ['category_id' => 1]), 'resolved', $staff);
        $this->backdate($oldResolved, now()->subDays(9));       // old but resolved -> not overdue
        $this->makeRequest($who, ['category_id' => 2]);          // new -> not overdue

        $manager = $this->manager();

        $this->actingAs($manager)->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('stats.total', 3)->where('stats.overdue', 1));

        $this->actingAs($manager)->get('/dashboard?category_id=2')->assertInertia(fn (Assert $page) => $page
            ->where('stats.total', 1)->where('stats.overdue', 0));
    }
}
