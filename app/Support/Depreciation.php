<?php

namespace App\Support;

use App\Models\Asset;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\CarbonInterface;

/**
 * Book-value maths for the asset register. Not an accounting ledger — it
 * computes a current value on demand from the asset's cost, method and dates.
 */
final class Depreciation
{
    /**
     * @return array{
     *   method: string, applicable: bool, currency: ?string,
     *   cost: ?string, salvage: string, months_elapsed: int,
     *   monthly: ?string, accumulated: ?string, book_value: ?string,
     *   fully_depreciated: bool
     * }
     */
    public static function forAsset(Asset $asset, ?CarbonInterface $asOf = null): array
    {
        $asOf ??= now();
        $method = $asset->depreciation_method ?: 'none';
        $cost = $asset->purchase_cost !== null ? (string) $asset->purchase_cost : null;
        $salvage = (string) BigDecimal::of((string) ($asset->salvage_value ?? '0'))->toScale(2, RoundingMode::HalfUp);
        $start = $asset->in_service_on ?: $asset->purchased_on;

        $base = [
            'method' => $method,
            'currency' => $asset->purchase_currency,
            'cost' => $cost,
            'salvage' => $salvage,
            'months_elapsed' => 0,
            'monthly' => null,
            'accumulated' => $cost !== null ? '0.00' : null,
            'book_value' => $cost,
            'fully_depreciated' => false,
        ];

        if ($cost === null || $method === 'none' || $start === null) {
            return ['applicable' => false] + $base;
        }

        $months = (int) max(0, floor($start->copy()->startOfMonth()->diffInMonths($asOf->copy()->startOfMonth())));

        $costDec = BigDecimal::of($cost);
        $salvageDec = BigDecimal::of($salvage);
        $depreciable = $costDec->minus($salvageDec);
        if ($depreciable->isNegative()) {
            $depreciable = BigDecimal::zero();
        }

        if ($method === 'straight_line') {
            $life = max(1, (int) $asset->useful_life_months);
            $monthly = $depreciable->dividedBy($life, 4, RoundingMode::HalfUp);
            $accumulated = $monthly->multipliedBy($months);
            if ($accumulated->isGreaterThan($depreciable)) {
                $accumulated = $depreciable;
            }
            $book = $costDec->minus($accumulated);
            $monthlyOut = (string) $monthly->toScale(2, RoundingMode::HalfUp);
        } else { // reducing_balance
            $rate = max(0.0, (float) ($asset->depreciation_rate ?? 0)) / 100;
            $factor = $rate >= 1 ? 0.0 : ($rate <= 0 ? 1.0 : (1 - $rate) ** ($months / 12));
            $book = $costDec->multipliedBy(BigDecimal::of((string) $factor));
            if ($book->isLessThan($salvageDec)) {
                $book = $salvageDec;
            }
            $accumulated = $costDec->minus($book);
            $monthlyOut = null;
        }

        return [
            'applicable' => true,
            'method' => $method,
            'currency' => $asset->purchase_currency,
            'cost' => (string) $costDec->toScale(2, RoundingMode::HalfUp),
            'salvage' => $salvage,
            'months_elapsed' => $months,
            'monthly' => $monthlyOut,
            'accumulated' => (string) $accumulated->toScale(2, RoundingMode::HalfUp),
            'book_value' => (string) $book->toScale(2, RoundingMode::HalfUp),
            'fully_depreciated' => $book->isLessThanOrEqualTo($salvageDec->plus('0.01')),
        ];
    }
}
