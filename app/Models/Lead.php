<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A contact-form submission being worked as a sales lead. Table stays
 * `contact_submissions` — the public site writes rows here.
 */
#[Fillable([
    'name', 'email', 'company', 'phone', 'message', 'source', 'status',
    'owner_id', 'notes', 'referred_by_client_id', 'referral_source',
])]
class Lead extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'contact_submissions';

    public const STATUSES = ['new', 'contacted', 'qualified', 'lost', 'converted'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function referredByClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'referred_by_client_id');
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    public function isConverted(): bool
    {
        return $this->converted_client_id !== null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'company', 'phone', 'source', 'status',
                'owner_id', 'notes', 'converted_client_id',
                'referred_by_client_id', 'referral_source',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'Lead received',
                'updated' => 'Lead updated',
                default => "Lead {$event}",
            });
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        $query->when($term !== '', fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('company', 'like', "%{$term}%");
        }));
    }
}
