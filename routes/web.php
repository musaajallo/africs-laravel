<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Support\PanelRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
|
| Marketing/content pages rendered from resources/js/Pages/Site/*. The CMS
| panel (/cms) and Console panel (/console) live in routes/panels/*.
|
*/

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

    return Inertia::render('Site/Home', [
        'canLogin' => Route::has('login'),
        'clientLogos' => $clientLogos,
    ]);
})->name('home');

Route::get('/client-logos/{filename}', function (string $filename) {
    $path = base_path('docs/company logos/'.basename($filename));

    abort_unless(File::exists($path), 404);

    return response()->file($path);
})->where('filename', '.*')->name('client-logo');

Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

$sitePages = [
    'portfolio' => ['/portfolio', 'Site/Portfolio'],
    'services.business' => ['/services/business', 'Site/Services/Business'],
    'services.technology' => ['/services/technology', 'Site/Services/Technology'],
    'services.design' => ['/services/design', 'Site/Services/Design'],
    'academy' => ['/academy', 'Site/Academy'],
    'limitless-africs' => ['/limitless-africs', 'Site/LimitlessAfrics'],
    'partnerships' => ['/partnerships', 'Site/Partnerships'],
    'network' => ['/network', 'Site/Network'],
    'careers' => ['/careers', 'Site/Careers'],
    'cookie-policy' => ['/cookie-policy', 'Site/CookiePolicy'],
];

foreach ($sitePages as $name => [$uri, $component]) {
    Route::get($uri, fn () => Inertia::render($component, [
        'canLogin' => Route::has('login'),
    ]))->name($name);
}

/*
|--------------------------------------------------------------------------
| Authenticated (shared)
|--------------------------------------------------------------------------
*/

// Kept for Breeze compatibility: bounces the user to whichever panel they
// can access (Console, then CMS, then the public site).
Route::get('/dashboard', fn (Request $request) => redirect()->to(PanelRouter::homeFor($request->user())))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
