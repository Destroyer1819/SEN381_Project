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

// TC-BB-01 | FR-003 | boundary values for the description length rule (min:10, max:2000): each limit and one either side
test('the description length is checked at its boundaries', function (int $length, bool $accepted) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/requests', validRequest(['description' => str_repeat('a', $length)]));

    if ($accepted) {
        $response->assertSessionHasNoErrors();
        expect(ServiceRequest::where('requestor_id', $user->id)->count())->toBe(1);
    } else {
        $response->assertSessionHasErrors('description');
        expect(ServiceRequest::where('requestor_id', $user->id)->count())->toBe(0);
    }
})->with([
    '9 characters' => [9, false],
    '10 characters' => [10, true],
    '2000 characters' => [2000, true],
    '2001 characters' => [2001, false],
]);

test('guests cannot submit a request', function () {
    $this->post('/requests', [])->assertRedirect('/login');
});
