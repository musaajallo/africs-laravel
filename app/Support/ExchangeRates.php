<?php

namespace App\Support;

use App\Models\ExchangeRate;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Reads FX rates for the finance modules. Everything is expressed against the
 * reporting base currency from Settings (currency.base, default GMD).
 */
final class ExchangeRates
{
    /**
     * The rate that converts 1 unit of $currency into the base currency, as of
     * $asOf (default today). Returns "1" when $currency already is the base.
     * Falls back to the most recent rate on or before the date; null if none
     * has ever been recorded.
     */
    public static function toBase(string $currency, ?CarbonInterface $asOf = null): ?string
    {
        $base = Settings::baseCurrency();
        $currency = strtoupper($currency);

        if ($currency === $base) {
            return '1';
        }

        $rate = ExchangeRate::query()
            ->where('base_currency', $base)
            ->where('quote_currency', $currency)
            ->where('rate_date', '<=', ($asOf ?? now())->toDateString())
            ->orderByDesc('rate_date')
            ->value('rate');

        return $rate !== null ? (string) $rate : null;
    }

    /**
     * The latest rate row per enabled non-base currency, for the FX screen.
     *
     * @return Collection<int, ExchangeRate>
     */
    public static function latestPerCurrency(): Collection
    {
        $base = Settings::baseCurrency();

        return collect(Settings::enabledCurrencies())
            ->reject(fn (string $currency) => $currency === $base)
            ->map(fn (string $currency) => ExchangeRate::query()
                ->where('base_currency', $base)
                ->where('quote_currency', $currency)
                ->orderByDesc('rate_date')
                ->first())
            ->filter()
            ->values();
    }
}
