<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Proforma;
use App\Models\Project;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Business-insight metrics for the Console dashboard. These are indicative
 * management figures, not accounting statements: money is summed in floating
 * point after converting to the reporting base currency, and a couple of
 * metrics (CAC, gross profit) lean on assumptions set in Settings.
 */
final class Analytics
{
    /** @var array<string, string> range key => human label */
    public const RANGES = [
        '30d' => 'Last 30 days',
        '90d' => 'Last 90 days',
        '12m' => 'Last 12 months',
        'ytd' => 'Year to date',
    ];

    private string $base;

    private float $marginPct;

    private float $monthlySpend;

    /** @var array<string, float> currency => rate to base */
    private array $fxCache = [];

    public function __construct()
    {
        $this->base = Settings::baseCurrency();
        $this->marginPct = Settings::grossMarginPct();
        $this->monthlySpend = Settings::monthlyAcquisitionSpend();
    }

    public static function normaliseRange(?string $key): string
    {
        return array_key_exists($key, self::RANGES) ? $key : '90d';
    }

    /**
     * @return array<string, mixed>
     */
    public function insights(string $rangeKey): array
    {
        $rangeKey = self::normaliseRange($rangeKey);
        $now = CarbonImmutable::now();

        $from = (match ($rangeKey) {
            '30d' => $now->subDays(30),
            '90d' => $now->subDays(90),
            '12m' => $now->subMonths(12),
            'ytd' => $now->startOfYear(),
        })->startOfDay();
        $to = $now->endOfDay();

        $lengthDays = max(1, $from->diffInDays($to));
        $prevTo = $from->subDay()->endOfDay();
        $prevFrom = $prevTo->subDays($lengthDays)->startOfDay();

        return [
            'range' => [
                'key' => $rangeKey,
                'label' => self::RANGES[$rangeKey],
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'prev_label' => $rangeKey === 'ytd'
                    ? 'same days last year'
                    : 'prev '.$this->spanLabel($rangeKey),
            ],
            'currency' => $this->base,
            'assumptions' => [
                'gross_margin_pct' => $this->marginPct,
                'monthly_acquisition_spend' => $this->monthlySpend,
                'configured' => $this->monthlySpend > 0,
            ],
            'revenue' => $this->revenue($from, $to, $prevFrom, $prevTo),
            'clients' => $this->clients($from, $to, $prevFrom, $prevTo),
            'projects' => $this->projects($from, $to, $prevFrom, $prevTo),
            'sales' => $this->sales($from, $to, $prevFrom, $prevTo),
        ];
    }

    /* ------------------------------------------------------------------ */

    /**
     * @return array<string, mixed>
     */
    private function revenue(
        CarbonImmutable $from,
        CarbonImmutable $to,
        CarbonImmutable $prevFrom,
        CarbonImmutable $prevTo,
    ): array {
        $collected = $this->collectedBetween($from, $to);
        $collectedPrev = $this->collectedBetween($prevFrom, $prevTo);
        $billed = $this->billedBetween($from, $to);
        $billedPrev = $this->billedBetween($prevFrom, $prevTo);

        return [
            'collected' => $collected,
            'collected_delta' => $this->delta($collected, $collectedPrev),
            'collected_prev' => $collectedPrev,
            'billed' => $billed,
            'billed_delta' => $this->delta($billed, $billedPrev),
            'collection_rate' => $billed > 0 ? round(min(1, $collected / $billed), 4) : null,
            'gross_profit' => round($collected * $this->marginPct / 100, 2),
            'series' => $this->cumulativeCollectedSeries($from, $to),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function clients(
        CarbonImmutable $from,
        CarbonImmutable $to,
        CarbonImmutable $prevFrom,
        CarbonImmutable $prevTo,
    ): array {
        $new = Client::whereBetween('created_at', [$from, $to])->count();
        $newPrev = Client::whereBetween('created_at', [$prevFrom, $prevTo])->count();

        // Lifetime value: total received per client, all time.
        $lifetimes = Payment::query()
            ->selectRaw('client_id, SUM(amount * fx_rate) as total, MIN(paid_on) as first_paid, MAX(paid_on) as last_paid')
            ->groupBy('client_id')
            ->get();

        $avgLtv = $lifetimes->isNotEmpty()
            ? round((float) $lifetimes->avg('total'), 2)
            : null;

        $avgLifespan = $lifetimes->isNotEmpty()
            ? round((float) $lifetimes->avg(function ($row) {
                $first = CarbonImmutable::parse($row->first_paid);
                $last = CarbonImmutable::parse($row->last_paid);

                return max(1, $first->diffInDays($last)) / 30.44;
            }), 1)
            : null;

        // CAC over the selected window.
        $months = max(0.25, $from->floatDiffInMonths($to));
        $spend = $this->monthlySpend * $months;
        $cac = ($this->monthlySpend > 0 && $new > 0) ? round($spend / $new, 2) : null;

        $ltvCac = ($cac !== null && $avgLtv !== null && $cac > 0)
            ? round(($avgLtv * $this->marginPct / 100) / $cac, 2)
            : null;

        $topClients = $this->topClientsByCollected($from, $to);
        $collected = $this->collectedBetween($from, $to);
        $concentration = ($collected > 0 && ! empty($topClients))
            ? round($topClients[0]['value'] / $collected, 4)
            : null;

        return [
            'active' => Client::where('status', 'active')->count(),
            'new' => $new,
            'new_prev' => $newPrev,
            'new_delta' => $this->delta($new, $newPrev),
            'paying' => $lifetimes->count(),
            'avg_ltv' => $avgLtv,
            'avg_lifespan_months' => $avgLifespan,
            'cac' => $cac,
            'ltv_cac_ratio' => $ltvCac,
            'top' => $topClients,
            'concentration' => $concentration,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projects(
        CarbonImmutable $from,
        CarbonImmutable $to,
        CarbonImmutable $prevFrom,
        CarbonImmutable $prevTo,
    ): array {
        $valued = Project::query()
            ->whereNotNull('budget_amount')
            ->where('status', '!=', 'cancelled')
            ->get(['budget_amount', 'budget_currency', 'service_line', 'status', 'created_at']);

        $toBase = fn ($row) => (float) $row->budget_amount * $this->fx($row->budget_currency);

        $avgValue = $valued->isNotEmpty()
            ? round($valued->avg($toBase), 2)
            : null;

        $inRange = $valued->filter(fn ($r) => $r->created_at?->between($from, $to));
        $inPrev = $valued->filter(fn ($r) => $r->created_at?->between($prevFrom, $prevTo));
        $avgValueDelta = ($inRange->isNotEmpty() && $inPrev->isNotEmpty())
            ? $this->delta($inRange->avg($toBase), $inPrev->avg($toBase))
            : null;

        $byLine = $valued
            ->groupBy('service_line')
            ->map(fn ($rows, $line) => [
                'label' => ProjectMeta::SERVICE_LINES[$line] ?? ucfirst((string) $line),
                'value' => round($rows->sum($toBase), 2),
                'caption' => $this->money($rows->sum($toBase)),
            ])
            ->sortByDesc('value')
            ->values()
            ->all();

        return [
            'avg_value' => $avgValue,
            'avg_value_delta' => $avgValueDelta,
            'valued_count' => $valued->count(),
            'pipeline_value' => round($valued->sum($toBase), 2),
            'by_service_line' => $byLine,
            'active' => Project::whereIn('status', ProjectMeta::OPEN_STATUSES)->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'won_in_range' => Project::where('status', 'active')
                ->whereBetween('updated_at', [$from, $to])->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sales(
        CarbonImmutable $from,
        CarbonImmutable $to,
        CarbonImmutable $prevFrom,
        CarbonImmutable $prevTo,
    ): array {
        // Proforma win rate — of the proformas that reached a decision.
        $decidedStatuses = ['accepted', 'converted', 'declined', 'expired'];
        $wonStatuses = ['accepted', 'converted'];

        $decided = Proforma::whereIn('status', $decidedStatuses)
            ->whereBetween('issue_date', [$from, $to])->count();
        $won = Proforma::whereIn('status', $wonStatuses)
            ->whereBetween('issue_date', [$from, $to])->count();

        $avgProforma = Proforma::whereBetween('issue_date', [$from, $to])
            ->whereNotIn('status', ['draft'])
            ->avg('base_total');

        $daysToPay = $this->avgDaysToPay($from, $to);
        $daysToPayPrev = $this->avgDaysToPay($prevFrom, $prevTo);

        $leads = Lead::whereBetween('created_at', [$from, $to]);
        $leadsTotal = (clone $leads)->count();
        $leadsConverted = (clone $leads)->whereNotNull('converted_client_id')->count();

        $byChannel = Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COALESCE(source, ?) as channel, COUNT(*) as c', ['unknown'])
            ->groupBy('channel')
            ->orderByDesc('c')
            ->get()
            ->map(fn ($r) => [
                'label' => ucfirst(str_replace('_', ' ', (string) $r->channel)),
                'value' => (int) $r->c,
            ])
            ->all();

        return [
            'proforma_win_rate' => $decided > 0 ? round($won / $decided, 4) : null,
            'proforma_decided' => $decided,
            'proforma_won' => $won,
            'avg_proforma_value' => $avgProforma !== null ? round((float) $avgProforma, 2) : null,
            'avg_days_to_pay' => $daysToPay !== null ? round((float) $daysToPay, 1) : null,
            'avg_days_to_pay_delta' => ($daysToPay !== null && $daysToPayPrev)
                ? $this->delta((float) $daysToPay, (float) $daysToPayPrev)
                : null,
            'lead_conversion' => $leadsTotal > 0 ? round($leadsConverted / $leadsTotal, 4) : null,
            'leads_total' => $leadsTotal,
            'leads_converted' => $leadsConverted,
            'leads_by_channel' => $byChannel,
        ];
    }

    /* ------------------------------------------------------------------ */

    /**
     * Average days from issue date to the invoice being settled in full, for
     * invoices issued in the window. Done in PHP so it runs on any driver.
     */
    private function avgDaysToPay(CarbonImmutable $from, CarbonImmutable $to): ?float
    {
        $settled = DB::table('payment_allocations as pa')
            ->join('payments as pay', 'pay.id', '=', 'pa.payment_id')
            ->groupBy('pa.invoice_id')
            ->selectRaw('pa.invoice_id, MAX(pay.paid_on) as settled_on')
            ->pluck('settled_on', 'invoice_id');

        $days = Invoice::query()
            ->where('status', 'paid')
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('id', $settled->keys())
            ->get(['id', 'issue_date'])
            ->map(fn (Invoice $i) => $i->issue_date->startOfDay()
                ->diffInDays(CarbonImmutable::parse($settled[$i->id])->startOfDay(), false))
            ->filter(fn ($d) => $d >= 0);

        return $days->isNotEmpty() ? round((float) $days->avg(), 1) : null;
    }

    private function collectedBetween(CarbonImmutable $from, CarbonImmutable $to): float
    {
        return (float) Payment::whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('COALESCE(SUM(amount * fx_rate), 0) as agg')
            ->value('agg');
    }

    private function billedBetween(CarbonImmutable $from, CarbonImmutable $to): float
    {
        return (float) Invoice::whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('status', ['draft', 'void'])
            ->selectRaw('COALESCE(SUM(base_total), 0) as agg')
            ->value('agg');
    }

    /**
     * Running total of cash collected, one point per day a payment landed — a
     * monotonic curve that fills the chart regardless of how lumpy or sparse
     * the payments are.
     *
     * @return list<array{label: string, value: float}>
     */
    private function cumulativeCollectedSeries(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $rows = Payment::whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('paid_on, SUM(amount * fx_rate) as agg')
            ->groupBy('paid_on')
            ->orderBy('paid_on')
            ->pluck('agg', 'paid_on');

        if ($rows->count() < 2) {
            return [];
        }

        $out = [];
        $running = 0.0;

        foreach ($rows as $date => $amount) {
            $running += (float) $amount;
            $out[] = [
                'label' => CarbonImmutable::parse($date)->format('j M'),
                'value' => round($running, 2),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{label: string, value: float, caption: string}>
     */
    private function topClientsByCollected(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return Payment::query()
            ->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
            ->join('clients', 'clients.id', '=', 'payments.client_id')
            ->groupBy('clients.id', 'clients.name')
            ->selectRaw('clients.name as name, SUM(payments.amount * payments.fx_rate) as total')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'label' => $r->name,
                'value' => round((float) $r->total, 2),
                'caption' => $this->money((float) $r->total),
            ])
            ->all();
    }

    private function fx(?string $currency): float
    {
        $currency = strtoupper((string) ($currency ?: $this->base));

        if ($currency === $this->base) {
            return 1.0;
        }

        return $this->fxCache[$currency] ??= (float) (ExchangeRates::toBase($currency) ?? 1);
    }

    private function delta(float $current, float $previous): ?float
    {
        // No pill when there is no prior period to compare against — a
        // "+100%" from a zero baseline is noise, not signal.
        if ($previous <= 0.0) {
            return null;
        }

        return round(($current - $previous) / $previous, 4);
    }

    private function spanLabel(string $rangeKey): string
    {
        return match ($rangeKey) {
            '30d' => '30 days',
            '90d' => '90 days',
            '12m' => '12 months',
            default => 'period',
        };
    }

    private function money(float $amount): string
    {
        return $this->base.' '.number_format($amount);
    }
}
