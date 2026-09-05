<?php

namespace App\Models;

use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'name', 'type', 'category', 'status', 'email', 'phone', 'website',
    'tax_number', 'currency', 'billing_address', 'city', 'country', 'notes',
    'owner_id',
])]
class Client extends Model
{
    use HasFactory, HasTags, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'country' => 'string',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'type', 'category', 'status', 'email', 'phone', 'website',
                'tax_number', 'currency', 'billing_address', 'city', 'country',
                'owner_id', 'notes',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'Client created',
                'updated' => 'Client details updated',
                'deleted' => 'Client archived',
                'restored' => 'Client restored',
                default => "Client {$event}",
            });
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function proformas(): HasMany
    {
        return $this->hasMany(Proforma::class);
    }

    public function primaryContact(): HasMany
    {
        return $this->contacts()->where('is_primary', true);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        $query->when($term !== '', fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('city', 'like', "%{$term}%");
        }));
    }
}
