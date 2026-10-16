<?php

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;

beforeEach(function () {
    $this->manager = User::factory()->create(['role' => 'management']); // sees every request
});

// Ids of the rows on the list page, in the order the server returned them
function listedIds($test, string $query = ''): array
{
    $response = $test->actingAs($test->manager)->get("/requests{$query}")->assertOk();

    return collect($response->viewData('page')['props']['requests']['data'])->pluck('id')->all();
}

// Three requests that sort differently on every field, so each field's order is distinguishable
function threeRequests(): array
{
    $water = Category::factory()->create(['name' => 'Water']);
    $access = Category::factory()->create(['name' => 'Access']);
    $lights = Category::factory()->create(['name' => 'Lights']);

    $a = ServiceRequest::factory()->create(['reference_number' => 2026000003, 'status' => 'resolved', 'category_id' => $water->id,
        'created_at' => now()->subDays(3), 'updated_at' => now()->subHours(1), 'resolved_at' => now()->subHours(1)]);
    $b = ServiceRequest::factory()->create(['reference_number' => 2026000001, 'status' => 'open', 'category_id' => $lights->id,
        'created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)]);
    $c = ServiceRequest::factory()->create(['reference_number' => 2026000002, 'status' => 'in_progress', 'category_id' => $access->id,
        'created_at' => now()->subDays(2), 'updated_at' => now()->subHours(5)]);

    return [$a->id, $b->id, $c->id];
}

// FR-005, FR-013: without a sort the list is newest first
test('the list is newest first by default', function () {
    [$a, $b, $c] = threeRequests();

    expect(listedIds($this))->toBe([$b, $c, $a]);
});

// FR-005, FR-013: every sort option the frontend offers, in both directions
test('the list can be sorted by each field', function (string $sort, string $direction, array $order) {
    $ids = threeRequests(); // [a, b, c]
    $expected = array_map(fn ($letter) => $ids[ord($letter) - ord('a')], $order);

    expect(listedIds($this, "?sort={$sort}&direction={$direction}"))->toBe($expected);
})->with([
    'oldest first' => ['created_at', 'asc', ['a', 'c', 'b']],
    'recently updated' => ['updated_at', 'desc', ['a', 'c', 'b']],
    'reference low to high' => ['reference', 'asc', ['b', 'c', 'a']],
    'reference high to low' => ['reference', 'desc', ['a', 'c', 'b']],
    'status open first (workflow order)' => ['status', 'asc', ['b', 'c', 'a']],
    'status closed first' => ['status', 'desc', ['a', 'c', 'b']],
    'category A to Z' => ['category', 'asc', ['c', 'b', 'a']],
]);

// The reason sorting is on the server: page 2 continues the order across the whole list
test('sorting applies across pages, not just the visible page', function () {
    foreach (range(1, 51) as $n) {
        ServiceRequest::factory()->create(['reference_number' => 2026000000 + $n]);
    }

    $firstPage = listedIds($this, '?sort=reference&direction=asc');
    $secondPage = listedIds($this, '?sort=reference&direction=asc&page=2');

    expect(ServiceRequest::whereIn('id', $firstPage)->max('reference_number'))->toBe(2026000050);
    expect(ServiceRequest::find($secondPage[0])->reference_number)->toBe(2026000051);
});

// Sorting and filtering work together
test('a sort keeps the active filters', function () {
    [$a, $b, $c] = threeRequests();

    expect(listedIds($this, '?status=open&sort=reference&direction=asc'))->toBe([$b]);
});

// Negative: only the listed fields are accepted, so user input never reaches ORDER BY
test('an unknown sort field or direction is rejected', function (string $query) {
    $this->actingAs($this->manager)
        ->get("/requests{$query}")
        ->assertSessionHasErrors();
})->with([
    'unknown field' => ['?sort=password&direction=asc'],
    'sql in field' => ['?sort=id;drop%20table%20users&direction=asc'],
    'unknown direction' => ['?sort=reference&direction=sideways'],
]);
