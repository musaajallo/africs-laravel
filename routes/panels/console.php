<?php

use App\Http\Controllers\Console\DashboardController;
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

    // User & access management. Individual actions are authorised by UserPolicy.
    Route::resource('users', UserController::class)->except('show');
});
