<?php

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

// PED 13.6.3, NFR-002: a refused action still gets a plain 403, and the attempt is written to the log
test('a refused action is logged with who tried what', function (string $method, Closure $path, string $route) {
    Log::spy();
    $requestor = User::factory()->create(['role' => 'requestor']);
    $request = ServiceRequest::factory()->create(); // belongs to someone else

    $this->actingAs($requestor)->call($method, $path($request))->assertForbidden();

    Log::shouldHaveReceived('warning')->once()->withArgs(fn (string $message, array $context) => $message === 'Access denied'
        && $context['user_id'] === $requestor->id
        && $context['role'] === 'requestor'
        && $context['method'] === $method
        && $context['route'] === $route);
    // Only the one warning line, not also Laravel's full error report with a stack trace
    Log::shouldNotHaveReceived('error');
})->with([
    'viewing another person\'s request (policy)' => ['GET', fn ($r) => "/requests/{$r->id}", 'requests.show'],
    'taking a request (policy)' => ['POST', fn ($r) => "/requests/{$r->id}/take", 'requests.take'],
    'opening the dashboard (gate middleware)' => ['GET', fn ($r) => '/dashboard', 'dashboard'],
]);

// Allowed actions are not logged as refusals
test('an allowed action is not logged', function () {
    Log::spy();
    $request = ServiceRequest::factory()->create();

    $this->actingAs($request->requestor)->get("/requests/{$request->id}")->assertOk();

    Log::shouldNotHaveReceived('warning');
});
