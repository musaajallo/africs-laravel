<?php

use App\Http\Controllers\Api\V1\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
|
| Authenticated with Sanctum personal-access tokens (or a session for a
| logged-in Console user). Token abilities mirror the permission slugs in
| App\Support\Rbac; read endpoints require the `*.view` ability, writes the
| `*.manage` ability. Errors are returned as JSON: { "message", "errors"? }.
|
*/

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->group(function () {
        Route::get('me', fn (Request $request) => [
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'abilities' => $request->user()->currentAccessToken()?->abilities ?? ['*'],
            ],
        ])->name('api.v1.me');

        Route::middleware('abilities:clients.view')->group(function () {
            Route::get('clients', [ClientController::class, 'index'])->name('api.v1.clients.index');
            Route::get('clients/{client}', [ClientController::class, 'show'])->name('api.v1.clients.show');
        });

        Route::middleware('abilities:clients.manage')->group(function () {
            Route::post('clients', [ClientController::class, 'store'])->name('api.v1.clients.store');
            Route::put('clients/{client}', [ClientController::class, 'update'])->name('api.v1.clients.update');
            Route::patch('clients/{client}', [ClientController::class, 'update']);
            Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('api.v1.clients.destroy');
        });
    });
