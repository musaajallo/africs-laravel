<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\ProjectController;
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

        Route::middleware('abilities:leads.view')->group(function () {
            Route::get('leads', [LeadController::class, 'index'])->name('api.v1.leads.index');
            Route::get('leads/{lead}', [LeadController::class, 'show'])->name('api.v1.leads.show');
        });

        Route::middleware('abilities:leads.manage')->group(function () {
            Route::post('leads', [LeadController::class, 'store'])->name('api.v1.leads.store');
        });

        Route::middleware('abilities:projects.view')->group(function () {
            Route::get('projects', [ProjectController::class, 'index'])->name('api.v1.projects.index');
            Route::get('projects/{project}', [ProjectController::class, 'show'])->name('api.v1.projects.show');
        });

        Route::middleware('abilities:projects.manage')->group(function () {
            Route::post('projects', [ProjectController::class, 'store'])->name('api.v1.projects.store');
            Route::put('projects/{project}', [ProjectController::class, 'update'])->name('api.v1.projects.update');
            Route::patch('projects/{project}', [ProjectController::class, 'update']);
            Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('api.v1.projects.destroy');
        });

        Route::middleware('abilities:clients.manage')->group(function () {
            Route::post('clients', [ClientController::class, 'store'])->name('api.v1.clients.store');
            Route::put('clients/{client}', [ClientController::class, 'update'])->name('api.v1.clients.update');
            Route::patch('clients/{client}', [ClientController::class, 'update']);
            Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('api.v1.clients.destroy');
        });
    });
