<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Proforma;
use App\Models\Project;
use App\Support\ActivityPresenter;
use App\Support\AssetMeta;
use App\Support\Money;
use App\Support\ProjectMeta;
use App\Support\Rbac;
use App\Support\Settings;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $base = Settings::baseCurrency();
        $today = now()->startOfDay();

        return Inertia::render('Console/Dashboard', [
            'base' => $base,
            'metrics' => $this->metrics($user, $base, $today),
            'overdueInvoices' => $user->can(Rbac::PERM_INVOICES_VIEW)
                ? $this->overdueInvoices($today)
                : null,
            'expiringProformas' => $user->can(Rbac::PERM_PROFORMAS_VIEW)
                ? $this->expiringProformas($today)
                : null,
            'activity' => $user->can(Rbac::PERM_ACTIVITY_VIEW)
                ? Activity::query()
                    ->with('causer:id,name')
                    ->latest()
                    ->limit(12)
                    ->get()
                    ->map(fn (Activity $a) => ActivityPresenter::present($a))
                : null,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function metrics($user, string $base, CarbonInterface $today): array
    {
        $metrics = [];

        if ($user->can(Rbac::PERM_INVOICES_VIEW)) {
            $outstanding = Invoice::query()
                ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
                ->get(['total', 'amount_paid', 'currency', 'fx_rate', 'due_date', 'status']);

            $owed = '0';
            $overdue = '0';

            foreach ($outstanding as $invoice) {
                $balance = Money::sum([$invoice->total, '-'.$invoice->amount_paid], $invoice->currency);
                if ((float) $balance <= 0) {
                    continue;
                }
                $baseBalance = Money::toBase($balance, $invoice->fx_rate, $base);
                $owed = Money::sum([$owed, $baseBalance], $base);

                if ($invoice->due_date && $invoice->due_date->lt($today)) {
                    $overdue = Money::sum([$overdue, $baseBalance], $base);
                }
            }

            $metrics[] = [
                'key' => 'outstanding', 'label' => 'Outstanding', 'currency' => $base,
                'value' => $owed, 'href' => route('console.receivables.index'),
            ];
            $metrics[] = [
                'key' => 'overdue', 'label' => 'Overdue', 'currency' => $base,
                'value' => $overdue, 'tone' => (float) $overdue > 0 ? 'danger' : null,
                'href' => route('console.invoices.index', ['status' => 'overdue']),
            ];
        }

        if ($user->can(Rbac::PERM_PAYMENTS_VIEW)) {
            $received = '0';
            Payment::query()
                ->whereBetween('paid_on', [now()->startOfMonth(), now()->endOfMonth()])
                ->get(['amount', 'currency', 'fx_rate'])
                ->each(function (Payment $p) use (&$received, $base) {
                    $received = Money::sum([$received, Money::toBase($p->amount, $p->fx_rate, $base)], $base);
                });

            $metrics[] = [
                'key' => 'received', 'label' => 'Received this month', 'currency' => $base,
                'value' => $received, 'href' => route('console.payments.index'),
            ];
        }

        if ($user->can(Rbac::PERM_PROFORMAS_VIEW)) {
            $metrics[] = [
                'key' => 'proformas', 'label' => 'Open proformas',
                'value' => (string) Proforma::whereIn('status', ['draft', 'sent', 'accepted'])->count(),
                'href' => route('console.proformas.index'),
            ];
        }

        if ($user->can(Rbac::PERM_LEADS_VIEW)) {
            $metrics[] = [
                'key' => 'leads', 'label' => 'Open leads',
                'value' => (string) Lead::whereIn('status', ['new', 'contacted', 'qualified'])->count(),
                'href' => route('console.leads.index'),
            ];
        }

        if ($user->can(Rbac::PERM_PROJECTS_VIEW)) {
            $metrics[] = [
                'key' => 'projects', 'label' => 'Active projects',
                'value' => (string) Project::whereIn('status', ProjectMeta::OPEN_STATUSES)->count(),
                'href' => route('console.projects.index'),
            ];
        }

        if ($user->can(Rbac::PERM_CLIENTS_VIEW)) {
            $metrics[] = [
                'key' => 'clients', 'label' => 'Clients',
                'value' => (string) Client::where('status', 'active')->count(),
                'href' => route('console.clients.index'),
            ];
        }

        if ($user->can(Rbac::PERM_ASSETS_VIEW)) {
            $bookValue = '0';
            $count = 0;
            Asset::query()
                ->whereIn('status', AssetMeta::ACTIVE_STATUSES)
                ->get()
                ->each(function (Asset $asset) use (&$bookValue, &$count, $base) {
                    $count++;
                    $dep = $asset->depreciation();
                    if ($dep['book_value'] !== null && ($asset->purchase_currency === $base || $asset->purchase_currency === null)) {
                        $bookValue = Money::sum([$bookValue, $dep['book_value']], $base);
                    }
                });

            $metrics[] = [
                'key' => 'assets', 'label' => 'Assets in use', 'currency' => $base,
                'value' => $bookValue, 'sub' => $count.' item'.($count === 1 ? '' : 's'),
                'href' => route('console.assets.index'),
            ];
        }

        return $metrics;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function overdueInvoices(CarbonInterface $today): array
    {
        return Invoice::query()
            ->with('client:id,name')
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->limit(6)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'client' => $invoice->client?->name,
                'currency' => $invoice->currency,
                'balance' => Money::sum([$invoice->total, '-'.$invoice->amount_paid], $invoice->currency),
                'due_date' => $invoice->due_date->toDateString(),
                'days_overdue' => (int) $invoice->due_date->startOfDay()->diffInDays($today),
            ])
            ->filter(fn ($row) => (float) $row['balance'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function expiringProformas(CarbonInterface $today): array
    {
        return Proforma::query()
            ->with('client:id,name')
            ->whereIn('status', ['sent', 'accepted'])
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '>=', $today)
            ->whereDate('valid_until', '<=', $today->copy()->addDays(7))
            ->orderBy('valid_until')
            ->limit(6)
            ->get()
            ->map(fn (Proforma $proforma) => [
                'id' => $proforma->id,
                'number' => $proforma->number,
                'client' => $proforma->client?->name,
                'currency' => $proforma->currency,
                'total' => $proforma->total,
                'valid_until' => $proforma->valid_until->toDateString(),
            ])
            ->all();
    }
}
