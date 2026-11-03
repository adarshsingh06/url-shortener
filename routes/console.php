<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('project:about', function () {
    $this->info('Sembark URL Shortener - Laravel 12');
})->purpose('Display the application name and framework version');
