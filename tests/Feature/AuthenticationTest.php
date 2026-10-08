<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Sign-in, sign-out and account settings (supports FR-008). */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // TC-API-17 | FR-008 | Valid credentials sign the user in and land on the request list
    public function test_user_can_sign_in_and_is_sent_to_the_request_list(): void
    {
        $user = User::factory()->create();

        $this->get('/login')->assertInertia(fn (Assert $page) => $page->component('auth/login'));

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/requests');
        $this->assertAuthenticatedAs($user);
    }

    // TC-API-18 | FR-008 | Negative: wrong password is rejected and nobody is signed in
    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'not-the-password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_sign_out(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_signed_in_users_are_redirected_away_from_the_login_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/requests');
    }

    public function test_profile_can_be_updated_but_not_to_an_email_already_in_use(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->patch('/settings/profile', ['name' => 'New Name', 'email' => 'new@example.test'])
            ->assertSessionHasNoErrors();
        $this->assertSame('New Name', $user->fresh()->name);

        $this->actingAs($user)->patch('/settings/profile', ['name' => 'New Name', 'email' => $other->email])
            ->assertSessionHasErrors('email');
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->create();
        $payload = ['password' => 'a-new-password-1', 'password_confirmation' => 'a-new-password-1'];

        $this->actingAs($user)->put('/settings/password', $payload + ['current_password' => 'wrong'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put('/settings/password', $payload + ['current_password' => 'password'])
            ->assertSessionHasNoErrors();
    }
}
