<?php

namespace App\Support;

use Brick\Math\RoundingMode;
use Brick\Money\Money as BrickMoney;

/**
 * Thin helpers over brick/money for the finance modules. Amounts are passed
 * and returned as plain decimal strings (matching the decimal(15,2) columns);
 * brick/money is used internally so rounding is correct and consistent.
 */
final class Money
{
    public static function of(string|int|float $amount, string $currency): BrickMoney
    {
        return BrickMoney::of($amount, strtoupper($currency), roundingMode: RoundingMode::HalfUp);
    }

    /** Unit price × quantity, rounded to the currency scale. */
    public static function multiply(string|int|float $amount, string|int|float $quantity, string $currency): string
    {
        return (string) self::of($amount, $currency)
            ->multipliedBy((string) $quantity, RoundingMode::HalfUp)
            ->getAmount();
    }

    /** A percentage of an amount (e.g. tax): amount × percent ÷ 100. */
    public static function percentage(string|int|float $amount, string|int|float $percent, string $currency): string
    {
        return (string) self::of($amount, $currency)
            ->multipliedBy((string) $percent, RoundingMode::HalfUp)
            ->dividedBy(100, RoundingMode::HalfUp)
            ->getAmount();
    }

    /**
     * Sum a list of decimal amounts in one currency.
     *
     * @param  iterable<string|int|float>  $amounts
     */
    public static function sum(iterable $amounts, string $currency): string
    {
        $total = self::of(0, $currency);

        foreach ($amounts as $amount) {
            $total = $total->plus(self::of($amount, $currency), RoundingMode::HalfUp);
        }

        return (string) $total->getAmount();
    }

    /** Convert an amount into the reporting base currency using a stored FX rate. */
    public static function toBase(string|int|float $amount, string|int|float $fxRate, string $baseCurrency): string
    {
        return (string) self::of($amount, $baseCurrency)
            ->multipliedBy((string) $fxRate, RoundingMode::HalfUp)
            ->getAmount();
    }

    /** Display form, e.g. "USD 1,250.00". No locale/symbol handling on purpose. */
    public static function format(string|int|float $amount, string $currency): string
    {
        return strtoupper($currency).' '.number_format((float) $amount, 2);
    }
}
