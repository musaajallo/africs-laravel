<?php

use App\Http\Controllers\Console\ActivityController;
use App\Http\Controllers\Console\ApiTokenController;
use App\Http\Controllers\Console\ClientController;
use App\Http\Controllers\Console\ContactController;
use App\Http\Controllers\Console\DashboardController;
use App\Http\Controllers\Console\ExchangeRateController;
use App\Http\Controllers\Console\InvoiceController;
use App\Http\Controllers\Console\LeadController;
use App\Http\Controllers\Console\PaymentController;
use App\Http\Controllers\Console\ProformaController;
use App\Http\Controllers\Console\ProjectController;
use App\Http\Controllers\Console\ReceivablesController;
use App\Http\Controllers\Console\RoadmapController;
use App\Http\Controllers\Console\SettingsController;
use App\Http\Controllers\Console\TagController;
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

    // Leads inbox.
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::put('leads/{lead}/triage', [LeadController::class, 'triage'])->name('leads.triage');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

    // Projects.
    Route::put('projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::resource('projects', ProjectController::class)->withTrashed(['show']);

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

    // Proformas (Finance).
    Route::put('proformas/{proforma}/restore', [ProformaController::class, 'restore'])->name('proformas.restore');
    Route::put('proformas/{proforma}/status', [ProformaController::class, 'status'])->name('proformas.status');
    Route::post('proformas/{proforma}/convert', [ProformaController::class, 'convert'])->name('proformas.convert');
    Route::get('proformas/{proforma}/pdf', [ProformaController::class, 'pdf'])->name('proformas.pdf');
    Route::resource('proformas', ProformaController::class)->withTrashed(['show']);

    // Invoices (Finance).
    Route::put('invoices/{invoice}/restore', [InvoiceController::class, 'restore'])->name('invoices.restore');
    Route::put('invoices/{invoice}/status', [InvoiceController::class, 'status'])->name('invoices.status');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');
    Route::resource('invoices', InvoiceController::class)->withTrashed(['show']);

    // Payments & receipts (Finance).
    Route::get('payments/invoices-for-client', [PaymentController::class, 'invoicesForClient'])->name('payments.invoices-for-client');
    Route::put('payments/{payment}/restore', [PaymentController::class, 'restore'])->name('payments.restore');
    Route::get('payments/{payment}/pdf', [PaymentController::class, 'pdf'])->name('payments.pdf');
    Route::resource('payments', PaymentController::class)->withTrashed(['show']);

    // Accounts receivable (Finance).
    Route::get('receivables', [ReceivablesController::class, 'index'])->name('receivables.index');

    // Exchange rates (Finance).
    Route::get('exchange-rates', [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
    Route::post('exchange-rates', [ExchangeRateController::class, 'store'])->name('exchange-rates.store');
    Route::post('exchange-rates/refresh', [ExchangeRateController::class, 'refresh'])->name('exchange-rates.refresh');

    // Settings.
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    // Tags — managed inline on the index page.
    Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);

    // Activity log (read-only).
    Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');

    // API tokens — a user manages their own.
    Route::get('api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
});
