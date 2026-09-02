<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'company', 'phone', 'message'])]
class ContactSubmission extends Model
{
    //
}
