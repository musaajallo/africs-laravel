<?php

namespace App\Support;

use App\Models\Sequence as SequenceModel;
use Illuminate\Support\Facades\DB;

/**
 * Hands out gap-free, per-series running numbers formatted as
 * {PREFIX}-{YEAR}-{0001}. Each (key, year) pair has one row holding the last
 * value used; next() locks that row for the duration of the surrounding
 * transaction so concurrent requests can't collide. The counter restarts at 1
 * each calendar year.
 */
final class Sequence
{
    public static function next(string $key, string $prefix, ?int $year = null): string
    {
        $year ??= (int) now()->year;

        return DB::transaction(function () use ($key, $prefix, $year) {
            $row = SequenceModel::query()
                ->where('key', $key)
                ->where('period', (string) $year)
                ->lockForUpdate()
                ->first();

            if (! $row) {
                $row = new SequenceModel(['key' => $key, 'period' => (string) $year, 'value' => 0]);
            }

            $row->value++;
            $row->save();

            return sprintf('%s-%d-%04d', $prefix, $year, $row->value);
        });
    }

    /** The next number this series would produce, without consuming it. */
    public static function peek(string $key, string $prefix, ?int $year = null): string
    {
        $year ??= (int) now()->year;

        $value = (int) SequenceModel::query()
            ->where('key', $key)
            ->where('period', (string) $year)
            ->value('value');

        return sprintf('%s-%d-%04d', $prefix, $year, $value + 1);
    }
}
