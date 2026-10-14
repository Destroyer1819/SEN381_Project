<?php


use App\Models\ServiceRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->requestor = User::factory()->create();
    $this->staff = User::factory()->create(['role' => 'staff']);
    $this->otherStaff = User::factory()->create(['role' => 'staff']);
    $this->manager = User::factory()->create(['role' => 'management']);

    $this->own = ServiceRequest::factory()->for($this->requestor, 'requestor')->create();
    $this->unassigned = ServiceRequest::factory()->create();
    $this->assignedToStaff = ServiceRequest::factory()->assignedTo($this->staff)->create();
    $this->assignedToOtherStaff = ServiceRequest::factory()->assignedTo($this->otherStaff)->create();
});

test('the list shows each role only what it may see', function (string $user, array $visible) {
    $expected = collect($visible)->map(fn ($name) => $this->{$name}->id)->sort()->values()->all();

    $this->actingAs($this->{$user})
        ->get('/requests')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('requests/index')
            ->where('requests.total', count($expected))
            ->where('requests.data', fn ($rows) => collect($rows)->pluck('id')->sort()->values()->all() === $expected));
})->with([
    'requestor' => ['requestor', ['own']],
    'staff' => ['staff', ['own', 'unassigned', 'assignedToStaff']],
    'management' => ['manager', ['own', 'unassigned', 'assignedToStaff', 'assignedToOtherStaff']],
]);

test('the detail page is only open to users who may see the request', function (string $user, string $request, int $status) {
    $this->actingAs($this->{$user})
        ->get('/requests/'.$this->{$request}->id)
        ->assertStatus($status);
})->with([
    ['requestor', 'own', 200],
    ['requestor', 'unassigned', 403],
    ['staff', 'unassigned', 200],
    ['staff', 'assignedToStaff', 200],
    ['staff', 'assignedToOtherStaff', 403],
    ['manager', 'assignedToOtherStaff', 200],
]);

test('guests are sent to the login page', function () {
    $this->get('/requests')->assertRedirect('/login');
    $this->get('/requests/'.$this->own->id)->assertRedirect('/login');
});

test('a missing request gives 404', function () {
    $this->actingAs($this->manager)->get('/requests/999999')->assertNotFound();
});
