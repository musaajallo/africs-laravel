<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

Route::get('/', function () {
    $logoExtensions = ['png', 'jpg', 'jpeg', 'svg', 'webp'];
    $clientLogosDir = base_path('docs/company logos');

    $clientLogos = collect(File::exists($clientLogosDir) ? File::files($clientLogosDir) : [])
        ->filter(fn ($file) => in_array(strtolower($file->getExtension()), $logoExtensions))
        ->sortBy(fn ($file) => $file->getFilename())
        ->values()
        ->map(fn ($file) => [
            'name' => Str::of($file->getFilenameWithoutExtension())->replace(['-', '_'], ' ')->title()->toString(),
            'src' => route('client-logo', ['filename' => $file->getFilename()]),
        ]);

    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'clientLogos' => $clientLogos,
    ]);
});

Route::get('/client-logos/{filename}', function (string $filename) {
    $path = base_path('docs/company logos/'.basename($filename));

    abort_unless(File::exists($path), 404);

    return response()->file($path);
})->where('filename', '.*')->name('client-logo');

Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/portfolio', function () {
    return Inertia::render('Portfolio', [
        'canLogin' => Route::has('login'),
    ]);
})->name('portfolio');

Route::get('/services/business', function () {
    return Inertia::render('Services/Business', [
        'canLogin' => Route::has('login'),
    ]);
})->name('services.business');

Route::get('/services/technology', function () {
    return Inertia::render('Services/Technology', [
        'canLogin' => Route::has('login'),
    ]);
})->name('services.technology');

Route::get('/services/design', function () {
    return Inertia::render('Services/Design', [
        'canLogin' => Route::has('login'),
    ]);
})->name('services.design');

Route::get('/cookie-policy', function () {
    return Inertia::render('CookiePolicy', [
        'canLogin' => Route::has('login'),
    ]);
})->name('cookie-policy');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
