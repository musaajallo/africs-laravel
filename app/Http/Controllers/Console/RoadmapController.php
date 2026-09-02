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
        'projects' => [
            'title' => 'Projects',
            'phase' => 'Phase 2',
            'blurb' => 'Engagements linked to a client and a service line (Business / Technology / Design), with status, dates, budget and an assigned team.',
        ],
        'proformas' => [
            'title' => 'Proformas',
            'phase' => 'Phase 3',
            'blurb' => 'Preliminary quotes with line items, per-document currency and tax. A proforma can be converted into an invoice while keeping the link.',
        ],
        'invoices' => [
            'title' => 'Invoices',
            'phase' => 'Phase 3',
            'blurb' => 'Tax invoices with their own numbering and lifecycle, PDF output on Africs letterhead, and states from draft through paid or overdue.',
        ],
        'payments' => [
            'title' => 'Payments & receipts',
            'phase' => 'Phase 4',
            'blurb' => 'Record payments against invoices in any currency; invoice status updates itself; generate a receipt PDF; an accounts-receivable view.',
        ],
        'subscriptions' => [
            'title' => 'Subscriptions & infrastructure',
            'phase' => 'Phase 5',
            'blurb' => 'A register of recurring digital services — hosting, domains, SaaS, licences — owned by Africs or run for a client, with renewal reminders.',
        ],
        'secrets' => [
            'title' => 'Secrets vault',
            'phase' => 'Phase 6',
            'blurb' => 'Encrypted credentials tied to subscriptions, revealed only after re-entering your password, with every reveal written to the audit trail.',
        ],
        'assets' => [
            'title' => 'Asset register',
            'phase' => 'Phase 7',
            'blurb' => 'Physical equipment Africs owns — laptops, desktops, printers — with purchase details, serial numbers, assignees and condition.',
        ],
        'api-tokens' => [
            'title' => 'API tokens',
            'phase' => 'Phase 0b',
            'blurb' => 'Issue and revoke scoped API tokens so other applications can integrate over the versioned /api/v1 endpoints.',
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
