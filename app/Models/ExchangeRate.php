<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single day's FX rate: the value of 1 unit of {@see $quote_currency}
 * expressed in {@see $base_currency} (the reporting base). Rows come either
 * from the daily exchangerate.host fetch or from a manual entry on the FX
 * screen; documents snapshot the rate they used so their totals never move.
 */
#[Fillable([
    'base_currency', 'quote_currency', 'rate', 'rate_date', 'source', 'created_by',
])]
class ExchangeRate extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:10',
            'rate_date' => 'date',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
