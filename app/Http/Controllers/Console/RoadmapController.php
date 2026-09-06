<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Placeholder pages for modules on the roadmap that aren't built yet. The
 * sidebar links here so the shape of the finished Console is visible while
 * it's being built. Remove each entry as its real module ships.
 */
class RoadmapController extends Controller
{
    public const MODULES = [
        'subscriptions' => [
            'title' => 'Subscriptions & infrastructure',
            'phase' => 'Phase 5',
            'blurb' => 'A register of recurring digital services — hosting, domains, SaaS, licences — owned by Africs or run for a client, with renewal reminders.',
        ],
    ];

    public function show(Request $request, string $module): Response
    {
        abort_unless(array_key_exists($module, self::MODULES), 404);

        return Inertia::render('Console/Roadmap', [
            'module' => ['key' => $module, ...self::MODULES[$module]],
        ]);
    }
}
