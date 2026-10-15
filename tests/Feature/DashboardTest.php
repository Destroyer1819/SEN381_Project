<?php

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('only management can open the dashboard', function (string $role, int $status) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)->get('/dashboard')->assertStatus($status);
})->with([
    ['requestor', 403],
    ['staff', 403],
    ['management', 200],
]);

test('the dashboard counts requests by status, category and overdue', function () {
    $manager = User::factory()->create(['role' => 'management']);
    $lostProperty = Category::factory()->create(['name' => 'Lost property']);

    ServiceRequest::factory()->count(2)->create(['category_id' => $lostProperty->id]);
    ServiceRequest::factory()->create(['status' => 'closed', 'resolved_at' => now()]);
    $old = ServiceRequest::factory()->create();
    $old->forceFill(['created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)])->save();

    $this->actingAs($manager)
        ->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('stats.total', 4)
            ->where('stats.by_status', ['open' => 3, 'assigned' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 1])
            ->where('stats.overdue', 1)
            ->where('stats.overdue_days', 7)
            ->where('stats.by_category.0', ['name' => 'Lost property', 'total' => 2]));

    $this->actingAs($manager)
        ->get("/dashboard?category_id={$lostProperty->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.total', 2)
            ->has('stats.by_category', 1));
});