<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\SettingsRequest;
use App\Support\Rbac;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        abort_unless(request()->user()->can(Rbac::PERM_SETTINGS_VIEW), 403);

        return Inertia::render('Console/Settings', [
            'settings' => Settings::all(),
            'supportedCurrencies' => Settings::SUPPORTED_CURRENCIES,
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can(Rbac::PERM_SETTINGS_MANAGE), 403);

        Settings::put($request->groups());

        return redirect()
            ->route('console.settings.edit')
            ->with('success', 'Settings saved.');
    }
}
