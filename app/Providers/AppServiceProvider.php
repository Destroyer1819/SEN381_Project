<?php

namespace App\Providers;

use App\Domain\Categories\CategoryRegistry;
use App\Domain\Categories\Handlers\DamagedEquipmentHandler;
use App\Domain\Categories\Handlers\FacilityFaultHandler;
use App\Domain\Categories\Handlers\ItSupportHandler;
use App\Domain\Categories\Handlers\LostPropertyHandler;
use App\Domain\Categories\Handlers\MaintenanceHandler;
use App\Domain\Categories\Handlers\OtherHandler;
use App\Domain\Categories\Handlers\SecurityConcernHandler;
use App\Models\User;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // D-010: category code => handler. Adding a category is one handler class plus one line here.
        $this->app->singleton(CategoryRegistry::class, fn () => new CategoryRegistry([
            101 => FacilityFaultHandler::class,
            102 => ItSupportHandler::class,
            103 => MaintenanceHandler::class,
            104 => LostPropertyHandler::class,
            105 => SecurityConcernHandler::class,
            106 => DamagedEquipmentHandler::class,
            107 => OtherHandler::class,
        ], fallback: OtherHandler::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Gate::define('view-dashboard', fn (User $user) => $user->role === 'management');

        // Windows names this variable "SystemRoot", but `artisan serve` only passes through
        // "SYSTEMROOT". Without it PHP's built-in server cannot open a socket.
        ServeCommand::$passthroughVariables[] = 'SystemRoot';

        // this is for staging/demo only, causing the email to be sent to one inbox instead of the seeded users.
        if (config('mail.demo_to')) {
            Mail::alwaysto(config('mail.demo_to'));
        }

    }
}
