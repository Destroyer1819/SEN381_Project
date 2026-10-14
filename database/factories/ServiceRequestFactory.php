<?php

namespace Database\Factories;

use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\User;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_number' => fake()->unique()->numberBetween(2026000001, 2026999999),
            'requestor_id' => User::factory(),
            'category_id' => Category::factory(),
            'location' => fake()->streetName(),
            'area' => fake()->randomElement(['North', 'South', 'East', 'West', 'Central']),
            'description' => fake()->sentence(8),
            'status' => 'open',
        ];
    }
    //shortcut so the test can write ServiceRequest::factory()->assignedTo($staff)->create()
    public function assignedTo(User $staff): static
    {
        return $this->state(['status' => 'assigned', 'assigned_to' => $staff->id]);
    }
}
