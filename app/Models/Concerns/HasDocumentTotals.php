<?php

namespace App\Models\Concerns;

use App\Support\Money;
use App\Support\Settings;

/**
 * Shared total maths for proformas and invoices. The model must expose a
 * `lines` relation whose rows have `quantity` and `unit_price`, and the
 * columns currency, fx_rate, tax_rate, subtotal, tax_total, total, base_total.
 */
trait HasDocumentTotals
{
    /**
     * Recompute each line total and the document totals from the current
     * lines, tax rate and FX rate. Does not persist — call save() after.
     */
    public function recalculateTotals(): void
    {
        $currency = $this->currency;

        $lineTotals = $this->lines->map(function ($line) use ($currency) {
            $line->line_total = Money::multiply($line->unit_price, $line->quantity, $currency);

            return $line->line_total;
        });

        $this->subtotal = Money::sum($lineTotals, $currency);
        $this->tax_total = Money::percentage($this->subtotal, $this->tax_rate, $currency);
        $this->total = Money::sum([$this->subtotal, $this->tax_total], $currency);
        $this->base_total = Money::toBase($this->total, $this->fx_rate, Settings::baseCurrency());
    }

    /** Persist recalculated totals, including any changed line totals. */
    public function saveWithTotals(): void
    {
        $this->recalculateTotals();

        $this->getConnection()->transaction(function () {
            $this->lines->each->save();
            $this->save();
        });
    }
}
