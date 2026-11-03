<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PublicRedirectController;
use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/accept-invitation/{token}', [InvitationController::class, 'showAccept'])
        ->name('invitations.accept.show');
    Route::post('/accept-invitation/{token}', [InvitationController::class, 'accept'])
        ->middleware('throttle:10,1')->name('invitations.accept');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/short-urls/download', [DashboardController::class, 'download'])->name('short-urls.download');
    Route::post('/short-urls', [ShortUrlController::class, 'store'])->name('short-urls.store');
    Route::post('/invitations/company', [InvitationController::class, 'inviteCompany'])->name('invitations.company');
    Route::post('/invitations/team', [InvitationController::class, 'inviteTeamMember'])->name('invitations.team');
});

// Keep this constrained route last: seven-character codes are publicly resolvable.
Route::get('/{code}', PublicRedirectController::class)
    ->where('code', '[a-z0-9]{7}') 
    ->name('short.redirect');
