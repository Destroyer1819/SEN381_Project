<?php

namespace Tests\Unit;

use App\Models\ServiceRequest;
use Carbon\Carbon;
use Tests\TestCase;

/** Unit tests for the overdue business rule (3 days by default) - no database needed. */
class OverdueRuleTest extends TestCase
{
    private function request(string $status, Carbon $created): ServiceRequest
    {
        $r = new ServiceRequest(['status' => $status]);
        $r->created_at = $created;

        return $r;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // TC-U-01 | FR-007 | Boundary: just inside / just outside the overdue limit
    public function test_open_request_becomes_overdue_just_after_the_limit(): void
    {
        Carbon::setTestNow('2026-10-10 12:00:00');

        $this->assertFalse($this->request('open', Carbon::parse('2026-10-07 12:01:00'))->isOverdue()); // 2d 23h 59m
        $this->assertTrue($this->request('open', Carbon::parse('2026-10-07 11:59:00'))->isOverdue());  // 3d 1m
    }

    // TC-U-02 | FR-007 | Finished requests are never overdue, however old
    public function test_resolved_and_closed_requests_are_never_overdue(): void
    {
        Carbon::setTestNow('2026-10-10 12:00:00');
        $old = Carbon::parse('2026-01-01');

        $this->assertFalse($this->request('resolved', $old)->isOverdue());
        $this->assertFalse($this->request('closed', $old)->isOverdue());
        $this->assertTrue($this->request('in_progress', $old)->isOverdue());
    }

    // TC-U-03 | The limit is configuration, not a hard-coded constant
    public function test_overdue_limit_is_configurable(): void
    {
        Carbon::setTestNow('2026-10-10 12:00:00');
        config(['civicconnect.overdue_days' => 10]);

        $this->assertFalse($this->request('open', Carbon::parse('2026-10-05'))->isOverdue());
        $this->assertTrue($this->request('open', Carbon::parse('2026-09-29'))->isOverdue());
    }
}
