<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 200+ so random test categories never collide with the real codes 101-107, which have their own handlers (D-010)
            'code' => fake()->unique()->numberBetween(200, 999),
            'name' => fake()->unique()->words(2, true),
            'is_active' => true,
        ];
    }
}
