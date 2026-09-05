<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\Money;
use App\Support\Rbac;
use App\Support\Settings;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReceivablesController extends Controller
{
    /** Buckets by age past the due date. */
    private const BUCKETS = ['not_due', 'd1_30', 'd31_60', 'd60_plus'];

    public function index(Request $request): Response
    {
        abort_unless($request->user()->can(Rbac::PERM_RECEIVABLES_VIEW), 403);

        $base = Settings::baseCurrency();
        $today = now()->startOfDay();

        $outstanding = Invoice::query()
            ->with('client:id,name')
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->get();

        $clients = [];
        $totals = array_fill_keys([...self::BUCKETS, 'total'], '0');

        foreach ($outstanding as $invoice) {
            $balance = $invoice->balance();

            if ((float) $balance <= 0) {
                continue;
            }

            $baseBalance = Money::toBase($balance, $invoice->fx_rate, $base);
            $bucket = $this->bucketFor($invoice, $today);

            $key = $invoice->client_id;
            $clients[$key] ??= [
                'client_id' => $invoice->client_id,
                'client' => $invoice->client?->name,
                ...array_fill_keys([...self::BUCKETS, 'total'], '0'),
                'invoices' => [],
            ];

            $clients[$key][$bucket] = Money::sum([$clients[$key][$bucket], $baseBalance], $base);
            $clients[$key]['total'] = Money::sum([$clients[$key]['total'], $baseBalance], $base);
            $clients[$key]['invoices'][] = [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'currency' => $invoice->currency,
                'balance' => $balance,
                'base_balance' => $baseBalance,
                'due_date' => $invoice->due_date?->toDateString(),
                'bucket' => $bucket,
            ];

            $totals[$bucket] = Money::sum([$totals[$bucket], $baseBalance], $base);
            $totals['total'] = Money::sum([$totals['total'], $baseBalance], $base);
        }

        usort($clients, fn ($a, $b) => (float) $b['total'] <=> (float) $a['total']);

        return Inertia::render('Console/Receivables/Index', [
            'base' => $base,
            'clients' => array_values($clients),
            'totals' => $totals,
            'buckets' => self::BUCKETS,
        ]);
    }

    private function bucketFor(Invoice $invoice, CarbonInterface $today): string
    {
        if (! $invoice->due_date) {
            return 'not_due';
        }

        $daysOverdue = $invoice->due_date->startOfDay()->diffInDays($today, false);

        return match (true) {
            $daysOverdue <= 0 => 'not_due',
            $daysOverdue <= 30 => 'd1_30',
            $daysOverdue <= 60 => 'd31_60',
            default => 'd60_plus',
        };
    }
}
