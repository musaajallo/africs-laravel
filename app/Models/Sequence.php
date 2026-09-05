<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per (key, period). Holds the last running number handed out for that
 * series. Read and written through {@see \App\Support\Sequence}, never directly.
 */
#[Fillable(['key', 'period', 'value'])]
class Sequence extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }
}
