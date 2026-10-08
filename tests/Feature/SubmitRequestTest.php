<?php

namespace Tests\Feature;

use App\Models\RequestStatusHistory;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\Fixtures;
use Tests\TestCase;

/** FR-001 - Request submission */
class SubmitRequestTest extends TestCase
{
    use RefreshDatabase, Fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCategories();
    }

    // TC-API-01 | FR-001 | AC: submitting creates a request with a unique ID and status "open"
    public function test_requestor_can_submit_a_valid_request(): void
    {
        $user = $this->requestor();

        $response = $this->actingAs($user)->post('/requests', $this->validPayload());

        $request = ServiceRequest::firstOrFail();
        $response->assertRedirect(route('requests.show', $request));
        $this->assertSame('open', $request->status);
        $this->assertSame(0, $request->version);
        $this->assertSame($user->id, $request->requestor_id);
        $this->assertSame(1001, $request->reference_number);

        $history = RequestStatusHistory::where('request_id', $request->id)->get();
        $this->assertCount(1, $history);
        $this->assertNull($history[0]->old_status);
        $this->assertSame('open', $history[0]->new_status);
    }

    public function test_reference_numbers_are_unique_and_increasing(): void
    {
        $user = $this->requestor();
        $this->actingAs($user)->post('/requests', $this->validPayload());
        $this->actingAs($user)->post('/requests', $this->validPayload());

        $this->assertSame([1001, 1002], ServiceRequest::orderBy('id')->pluck('reference_number')->all());
    }

    // TC-BB-01 | FR-001 | Boundary value analysis on description length (min 10, max 2000)
    #[DataProvider('descriptionBoundaries')]
    public function test_description_length_boundaries(int $length, bool $valid): void
    {
        $response = $this->actingAs($this->requestor())
            ->post('/requests', $this->validPayload(['description' => str_repeat('a', $length)]));

        $valid ? $response->assertSessionDoesntHaveErrors('description') : $response->assertSessionHasErrors('description');
        $this->assertSame($valid ? 1 : 0, ServiceRequest::count());
    }

    public static function descriptionBoundaries(): array
    {
        return [
            'below minimum (9)' => [9, false],
            'at minimum (10)' => [10, true],
            'at maximum (2000)' => [2000, true],
            'above maximum (2001)' => [2001, false],
        ];
    }

    // TC-BB-02 | FR-001 | Boundary value analysis on location length (max 255)
    #[DataProvider('locationBoundaries')]
    public function test_location_length_boundaries(int $length, bool $valid): void
    {
        $response = $this->actingAs($this->requestor())
            ->post('/requests', $this->validPayload(['location' => str_repeat('l', $length)]));

        $valid ? $response->assertSessionDoesntHaveErrors('location') : $response->assertSessionHasErrors('location');
    }

    public static function locationBoundaries(): array
    {
        return [
            'empty' => [0, false],
            'one char' => [1, true],
            'at maximum (255)' => [255, true],
            'above maximum (256)' => [256, false],
        ];
    }

    // TC-BB-03 | FR-001 | Equivalence partitions for category: valid / missing / non-existent / inactive
    public function test_category_must_be_a_valid_active_category(): void
    {
        $user = $this->requestor();

        $this->actingAs($user)->post('/requests', $this->validPayload(['category_id' => null]))->assertSessionHasErrors('category_id');
        $this->actingAs($user)->post('/requests', $this->validPayload(['category_id' => 999]))->assertSessionHasErrors('category_id');

        \App\Models\Category::where('id', 2)->update(['is_active' => false]);
        $this->actingAs($user)->post('/requests', $this->validPayload(['category_id' => 2]))->assertSessionHasErrors('category_id');

        $this->assertSame(0, ServiceRequest::count());
    }

    // TC-API-02 | FR-008 | Negative: unauthenticated users cannot submit
    public function test_guest_cannot_submit_and_is_sent_to_login(): void
    {
        $this->post('/requests', $this->validPayload())->assertRedirect('/login');
        $this->assertSame(0, ServiceRequest::count());
    }
}
