<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Support\Settings;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pulls the day's rates for every enabled currency from exchangerate.host and
 * upserts one {@see ExchangeRate} row per currency (value of 1 foreign unit in
 * the reporting base currency). Failures are logged, not thrown — manual entry
 * on the FX screen is always the fallback.
 */
class ExchangeRateFetcher
{
    /**
     * @return array{fetched: list<string>, skipped: list<string>, date: ?string}
     */
    public function fetch(): array
    {
        $base = Settings::baseCurrency();
        $currencies = collect(Settings::enabledCurrencies())
            ->reject(fn (string $c) => $c === $base)
            ->values();

        if ($currencies->isEmpty()) {
            return ['fetched' => [], 'skipped' => [], 'date' => null];
        }

        $response = Http::baseUrl(rtrim((string) config('services.exchangerate.url'), '/'))
            ->acceptJson()
            ->get('/latest', array_filter([
                'base' => $base,
                'symbols' => $currencies->implode(','),
                'access_key' => config('services.exchangerate.key'),
            ]));

        $rates = $response->json('rates');

        if ($response->failed() || ! is_array($rates)) {
            Log::warning('Exchange rate fetch failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return ['fetched' => [], 'skipped' => $currencies->all(), 'date' => null];
        }

        $date = $response->json('date') ?: now()->toDateString();
        $fetched = [];
        $skipped = [];

        foreach ($currencies as $currency) {
            $perBase = $rates[$currency] ?? null; // 1 base = $perBase of $currency

            if (! is_numeric($perBase) || (float) $perBase <= 0) {
                $skipped[] = $currency;

                continue;
            }

            ExchangeRate::updateOrCreate(
                ['base_currency' => $base, 'quote_currency' => $currency, 'rate_date' => $date],
                [
                    // Store the inverse: value of 1 unit of $currency in the base.
                    'rate' => (string) BigDecimal::one()->dividedBy((string) $perBase, 10, RoundingMode::HalfUp),
                    'source' => 'exchangerate.host',
                ],
            );

            $fetched[] = $currency;
        }

        return ['fetched' => $fetched, 'skipped' => $skipped, 'date' => $date];
    }
}
