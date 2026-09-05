<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ExchangeRateRequest;
use App\Models\ExchangeRate;
use App\Services\ExchangeRateFetcher;
use App\Support\Rbac;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeRateController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can(Rbac::PERM_EXCHANGE_RATES_VIEW), 403);

        $base = Settings::baseCurrency();
        $foreign = array_values(array_diff(Settings::enabledCurrencies(), [$base]));

        $rates = collect($foreign)->map(fn (string $currency) => [
            'currency' => $currency,
            'history' => ExchangeRate::query()
                ->where('base_currency', $base)
                ->where('quote_currency', $currency)
                ->orderByDesc('rate_date')
                ->limit(12)
                ->get(['id', 'rate', 'rate_date', 'source'])
                ->map(fn (ExchangeRate $rate) => [
                    'id' => $rate->id,
                    'rate' => (string) $rate->rate,
                    'rate_date' => $rate->rate_date->toDateString(),
                    'source' => $rate->source,
                ]),
        ]);

        return Inertia::render('Console/ExchangeRates/Index', [
            'base' => $base,
            'rates' => $rates,
            'canManage' => $request->user()->can(Rbac::PERM_EXCHANGE_RATES_MANAGE),
        ]);
    }

    public function store(ExchangeRateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        ExchangeRate::updateOrCreate(
            [
                'base_currency' => Settings::baseCurrency(),
                'quote_currency' => $data['currency'],
                'rate_date' => $data['rate_date'],
            ],
            [
                'rate' => $data['rate'],
                'source' => 'manual',
                'created_by' => $request->user()->id,
            ],
        );

        return back()->with('success', "Rate for {$data['currency']} saved.");
    }

    public function refresh(Request $request, ExchangeRateFetcher $fetcher): RedirectResponse
    {
        abort_unless($request->user()->can(Rbac::PERM_EXCHANGE_RATES_MANAGE), 403);

        $result = $fetcher->fetch();

        if ($result['fetched'] === []) {
            return back()->with('error', 'Could not fetch rates. Check the API key, or enter them manually below.');
        }

        return back()->with('success', 'Fetched rates for '.implode(', ', $result['fetched']).'.');
    }
}
