<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflow;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

trait Fixtures
{
    protected function seedCategories(): void
    {
        foreach (DatabaseSeeder::CATEGORIES as $id => [$code, $name]) {
            Category::updateOrCreate(['id' => $id], ['code' => $code, 'name' => $name, 'is_active' => true]);
        }
    }

    protected function requestor(): User
    {
        return User::factory()->create();
    }

    protected function staff(): User
    {
        return User::factory()->staff()->create();
    }

    protected function manager(): User
    {
        return User::factory()->management()->create();
    }

    protected function validPayload(array $override = []): array
    {
        return array_merge([
            'category_id' => 1,
            'location' => 'Main hall',
            'area' => 'North wing',
            'description' => 'The ceiling light flickers constantly.',
        ], $override);
    }

    protected function makeRequest(User $requestor, array $override = []): ServiceRequest
    {
        return app(RequestWorkflow::class)->create($requestor, $this->validPayload($override));
    }

    /** Puts a request straight into a status (test set-up only, bypasses the workflow on purpose). */
    protected function forceStatus(ServiceRequest $request, string $status, ?User $assignee = null): ServiceRequest
    {
        DB::table('service_request')->where('id', $request->id)->update([
            'status' => $status,
            'assigned_to' => $status === 'open' ? null : ($assignee?->id),
            'resolved_at' => in_array($status, ['resolved', 'closed'], true) ? now() : null,
            'updated_at' => now(),
        ]);

        return $request->fresh();
    }

    protected function backdate(ServiceRequest $request, \DateTimeInterface $when): void
    {
        DB::table('service_request')->where('id', $request->id)->update(['created_at' => $when, 'updated_at' => $when]);
    }
}
