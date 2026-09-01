<?php

use App\Http\Controllers\Cms\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CMS panel (/cms)
|--------------------------------------------------------------------------
|
| Manages public website content. URL prefix "cms/" and route-name prefix
| "cms." are applied in bootstrap/app.php. Every route here requires an
| authenticated, verified user holding the `cms.access` permission.
|
*/

Route::middleware(['auth', 'verified', 'panel:cms'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});
