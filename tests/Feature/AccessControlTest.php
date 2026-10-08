<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\Fixtures;
use Tests\TestCase;

/** FR-002 (own requests only) and FR-008 (role-based access) */
class AccessControlTest extends TestCase
{
    use RefreshDatabase, Fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCategories();
    }

    // TC-API-03 | FR-002 | AC: requester sees only their own requests
    public function test_requestor_list_contains_only_own_requests(): void
    {
        $alice = $this->requestor();
        $bob = $this->requestor();
        $this->makeRequest($alice);
        $this->makeRequest($alice);
        $this->makeRequest($bob);

        $this->actingAs($alice)->get('/requests')->assertInertia(fn (Assert $page) => $page
            ->component('requests/index')
            ->has('requests.data', 2)
            ->where('requests.total', 2));
    }

    // TC-API-04 | FR-002 / FR-008 | Negative: another requestor's request is forbidden
    public function test_requestor_cannot_open_someone_elses_request(): void
    {
        $alice = $this->requestor();
        $bob = $this->requestor();
        $request = $this->makeRequest($bob);

        $this->actingAs($alice)->get('/requests/'.$request->id)->assertForbidden();
        $this->actingAs($bob)->get('/requests/'.$request->id)->assertOk();
        $this->actingAs($this->staff())->get('/requests/'.$request->id)->assertOk();
    }

    // TC-API-05 | FR-008 | Negative: management-only dashboard
    public function test_dashboard_is_management_only(): void
    {
        $this->actingAs($this->requestor())->get('/dashboard')->assertForbidden();
        $this->actingAs($this->staff())->get('/dashboard')->assertForbidden();
        $this->actingAs($this->manager())->get('/dashboard')->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/requests')->assertRedirect('/login');
    }

    // TC-API-06 | FR-008 | Negative: a requestor cannot drive the staff workflow
    public function test_requestor_cannot_take_or_change_status(): void
    {
        $alice = $this->requestor();
        $request = $this->makeRequest($alice);

        $this->actingAs($alice)->post("/requests/{$request->id}/take")->assertForbidden();
        $this->actingAs($alice)->post("/requests/{$request->id}/status", ['status' => 'closed', 'version' => 0])->assertForbidden();

        $this->assertSame('open', $request->fresh()->status);
    }

    // TC-API-07 | FR-008 | Security: self-registration cannot escalate privileges
    public function test_registration_always_creates_a_requestor(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky', 'email' => 'sneaky@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'management',
        ])->assertRedirect('/requests');

        $this->assertSame('requestor', User::where('email', 'sneaky@example.test')->value('role'));
    }
}
