<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/** Row in the polymorphic `taggables` table — used only for counting. */
class Taggable extends Pivot
{
    protected $table = 'taggables';

    public $timestamps = false;
}
