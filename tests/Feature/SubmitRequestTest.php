<?php

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;

function validRequest(array $overrides = []): array
{
    return [
        'category_id' => Category::factory()->create()->id,
        'location' => 'Room 204',
        'area' => 'Admin block',
        'description' => 'Projector does not turn on.',
        ...$overrides,
    ];
}

test('a signed-in user can submit a request', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/requests', validRequest());

    $created = ServiceRequest::first();

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect("/requests/{$created->id}")
        ->assertSessionHas('success');

    expect($created->requestor_id)->toBe($user->id);
    expect($created->fresh()->status)->toBe('open');
    expect($created->history()->count())->toBe(1);
});

test('an empty submission shows the custom messages and saves nothing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/requests', []);

    $response->assertSessionHasErrors([
        'category_id' => 'Choose a category.',
        'location' => 'Enter the location.',
        'area' => 'Enter the area.',
        'description' => 'Describe the problem.',
    ]);

    expect(ServiceRequest::count())->toBe(0);
});

test('an unknown category is rejected with the custom message', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/requests', validRequest(['category_id' => 999]));

    $response->assertSessionHasErrors(['category_id' => 'Choose a valid category.']);
});

test('a description under 10 characters is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/requests', validRequest(['description' => 'too short']));

    $response->assertSessionHasErrors('description');
});

test('guests cannot submit a request', function () {
    $this->post('/requests', [])->assertRedirect('/login');
});
