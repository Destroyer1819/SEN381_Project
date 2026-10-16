<?php

use App\Domain\Categories\CategoryRegistry;
use App\Domain\Categories\Handlers\DamagedEquipmentHandler;
use App\Domain\Categories\Handlers\FacilityFaultHandler;
use App\Domain\Categories\Handlers\ItSupportHandler;
use App\Domain\Categories\Handlers\LostPropertyHandler;
use App\Domain\Categories\Handlers\MaintenanceHandler;
use App\Domain\Categories\Handlers\OtherHandler;
use App\Domain\Categories\Handlers\SecurityConcernHandler;
use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;
use Database\Seeders\CategorySeeder;

function submitIn(int $code, int $descriptionLength)
{
    $category = Category::factory()->create(['code' => $code]);

    return test()->actingAs(User::factory()->create())->post('/requests', [
        'category_id' => $category->id,
        'location' => 'Main gate',
        'area' => 'North',
        'description' => str_repeat('a', $descriptionLength),
    ]);
}

// D-010: each controlled category code resolves to its own handler
test('each category code resolves to its own handler', function (int $code, string $handler) {
    expect(app(CategoryRegistry::class)->forCode($code))->toBeInstanceOf($handler);
})->with([
    'Facility faults' => [101, FacilityFaultHandler::class],
    'IT support' => [102, ItSupportHandler::class],
    'Maintenance' => [103, MaintenanceHandler::class],
    'Lost property' => [104, LostPropertyHandler::class],
    'Security concerns' => [105, SecurityConcernHandler::class],
    'Damaged equipment' => [106, DamagedEquipmentHandler::class],
    'Other' => [107, OtherHandler::class],
]);

// D-010 negative: an unknown code falls back to Other instead of failing
test('an unknown category code falls back to the Other handler', function () {
    expect(app(CategoryRegistry::class)->forCode(999))->toBeInstanceOf(OtherHandler::class);
    expect(app(CategoryRegistry::class)->forCategoryId('not-a-number'))->toBeInstanceOf(OtherHandler::class);
});

// R-007 regression: a category added to the seeder without a handler would silently use Other
test('every seeded category has its own handler', function () {
    $this->seed(CategorySeeder::class);

    expect(Category::pluck('code')->sort()->values()->all())
        ->toBe(collect(app(CategoryRegistry::class)->codes())->sort()->values()->all());
});

// D-010 boundary values: security concerns need at least 30 characters, other categories still 10
test('the description minimum depends on the category', function (int $code, int $length, bool $accepted) {
    $response = submitIn($code, $length);

    if ($accepted) {
        $response->assertSessionHasNoErrors();
        expect(ServiceRequest::count())->toBe(1);
    } else {
        $response->assertSessionHasErrors('description');
        expect(ServiceRequest::count())->toBe(0);
    }
})->with([
    'security, 29 characters' => [105, 29, false],
    'security, 30 characters' => [105, 30, true],
    'lost property, 19 characters' => [104, 19, false],
    'lost property, 20 characters' => [104, 20, true],
    'facility fault, 10 characters' => [101, 10, true],
    'other, 10 characters' => [107, 10, true],
]);

// NFR-008: the message says what the category needs, not just "too short"
test('a too-short security report gets the category message', function () {
    submitIn(105, 15)->assertSessionHasErrors([
        'description' => 'For a security concern, describe what happened, when and who was involved in at least 30 characters.',
    ]);
});

// The general limits still apply on top of the category rules
test('category rules do not remove the general description limit', function () {
    submitIn(105, 2001)->assertSessionHasErrors('description');
    expect(ServiceRequest::count())->toBe(0);
});
