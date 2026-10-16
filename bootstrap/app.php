<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Render terminates HTTPS in front of the app. this allows it to trust its forward headers.
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // PED 13.6.3 (FR-011, FR-018, NFR-002): a policy refusal still returns a generic 403,
        // but the attempt is logged. Laravel ignores AuthorizationException by default, so opt back in.
        $exceptions->stopIgnoring(AuthorizationException::class);

        $exceptions->report(function (AuthorizationException $e) {
            $request = request();

            Log::warning('Access denied', [
                'user_id' => $request->user()?->id,
                'role' => $request->user()?->role,
                'method' => $request->method(),
                'path' => $request->path(),
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
            ]);
        })->stop(); // one warning line, not Laravel's full error report with a stack trace
    })->create();
