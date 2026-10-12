<?php

namespace App\Providers;

use Illuminate\Foundation\Console\ServeCommand;
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

        // Windows names this variable "SystemRoot", but `artisan serve` only passes through
        // "SYSTEMROOT". Without it PHP's built-in server cannot open a socket.
        ServeCommand::$passthroughVariables[] = 'SystemRoot';
    }
}
