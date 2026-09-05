<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ExchangeRateController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\ProformaController;
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

        Route::middleware('abilities:exchange-rates.view')->group(function () {
            Route::get('exchange-rates', [ExchangeRateController::class, 'index'])->name('api.v1.exchange-rates.index');
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

        Route::middleware('abilities:proformas.view')->group(function () {
            Route::get('proformas', [ProformaController::class, 'index'])->name('api.v1.proformas.index');
            Route::get('proformas/{proforma}', [ProformaController::class, 'show'])->name('api.v1.proformas.show');
        });

        Route::middleware('abilities:proformas.manage')->group(function () {
            Route::post('proformas', [ProformaController::class, 'store'])->name('api.v1.proformas.store');
            Route::put('proformas/{proforma}', [ProformaController::class, 'update'])->name('api.v1.proformas.update');
            Route::patch('proformas/{proforma}', [ProformaController::class, 'update']);
            Route::delete('proformas/{proforma}', [ProformaController::class, 'destroy'])->name('api.v1.proformas.destroy');
        });

        Route::middleware('abilities:invoices.view')->group(function () {
            Route::get('invoices', [InvoiceController::class, 'index'])->name('api.v1.invoices.index');
            Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('api.v1.invoices.show');
        });

        Route::middleware('abilities:invoices.manage')->group(function () {
            Route::post('invoices', [InvoiceController::class, 'store'])->name('api.v1.invoices.store');
            Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('api.v1.invoices.update');
            Route::patch('invoices/{invoice}', [InvoiceController::class, 'update']);
            Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('api.v1.invoices.destroy');
            Route::post('proformas/{proforma}/convert', [InvoiceController::class, 'convert'])->name('api.v1.proformas.convert');
        });

        Route::middleware('abilities:clients.manage')->group(function () {
            Route::post('clients', [ClientController::class, 'store'])->name('api.v1.clients.store');
            Route::put('clients/{client}', [ClientController::class, 'update'])->name('api.v1.clients.update');
            Route::patch('clients/{client}', [ClientController::class, 'update']);
            Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('api.v1.clients.destroy');
        });
    });
