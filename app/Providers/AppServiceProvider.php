<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Application services can be bound here as the project grows.
    }

    public function boot(): void
    {
        // No global boot-time behavior is needed for the assignment.
    }
}
