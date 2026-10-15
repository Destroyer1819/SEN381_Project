<?php

namespace App\Providers;

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
        //
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
