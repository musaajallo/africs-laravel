<?php

use App\Http\Controllers\Console\ClientController;
use App\Http\Controllers\Console\ContactController;
use App\Http\Controllers\Console\DashboardController;
use App\Http\Controllers\Console\RoadmapController;
use App\Http\Controllers\Console\SettingsController;
use App\Http\Controllers\Console\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Console panel (/console)
|--------------------------------------------------------------------------
|
| The internal ERP. URL prefix "console/" and route-name prefix "console."
| are applied in bootstrap/app.php. Every route here requires an
| authenticated, verified user holding the `console.access` permission.
|
*/

Route::middleware(['auth', 'verified', 'panel:console'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Placeholder pages for roadmap modules not built yet. Remove per module.
    Route::get('roadmap/{module}', [RoadmapController::class, 'show'])->name('roadmap');

    // Clients & contacts. Actions are authorised by ClientPolicy.
    Route::put('clients/{client}/restore', [ClientController::class, 'restore'])
        ->name('clients.restore');
    Route::resource('clients', ClientController::class)->withTrashed(['show']);

    // Per-contact CRUD from the client detail page.
    Route::post('clients/{client}/contacts', [ContactController::class, 'store'])
        ->name('clients.contacts.store');
    Route::put('clients/{client}/contacts/{contact}', [ContactController::class, 'update'])
        ->name('clients.contacts.update');
    Route::delete('clients/{client}/contacts/{contact}', [ContactController::class, 'destroy'])
        ->name('clients.contacts.destroy');

    // User & access management. Individual actions are authorised by UserPolicy.
    Route::resource('users', UserController::class)->except('show');

    // Settings.
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
});
