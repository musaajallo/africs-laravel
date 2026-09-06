<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'name', 'category', 'make', 'model', 'serial_number', 'asset_tag',
    'status', 'condition', 'purchased_on', 'purchase_cost', 'purchase_currency',
    'supplier', 'warranty_until', 'location', 'notes',
])]
class Asset extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /** @var array<string, mixed> */
    protected $attributes = [
        'category' => 'other',
        'status' => 'spare',
    ];

    protected function casts(): array
    {
        return [
            'purchased_on' => 'date',
            'warranty_until' => 'date',
            'assigned_on' => 'date',
            'purchase_cost' => 'decimal:2',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->latest('assigned_on')->latest('id');
    }

    public function openAssignment(): HasMany
    {
        return $this->assignments()->whereNull('returned_on');
    }

    /**
     * Hand the asset to a user: close any open assignment, open a new one, and
     * flip the asset to "in use".
     */
    public function assignTo(User $user, ?Carbon $on = null, ?string $notes = null): void
    {
        $on ??= now();

        $this->getConnection()->transaction(function () use ($user, $on, $notes) {
            $this->openAssignment()->update(['returned_on' => $on->toDateString()]);

            $this->assignments()->create([
                'user_id' => $user->id,
                'assigned_on' => $on->toDateString(),
                'notes' => $notes,
            ]);

            $this->forceFill([
                'assigned_to' => $user->id,
                'assigned_on' => $on->toDateString(),
                'status' => $this->status === 'retired' || $this->status === 'lost' ? $this->status : 'in_use',
            ])->save();
        });
    }

    /** Return the asset: close the open assignment and mark it spare. */
    public function unassign(?Carbon $on = null): void
    {
        $on ??= now();

        $this->getConnection()->transaction(function () use ($on) {
            $this->openAssignment()->update(['returned_on' => $on->toDateString()]);

            $this->forceFill([
                'assigned_to' => null,
                'assigned_on' => null,
                'status' => in_array($this->status, ['retired', 'lost'], true) ? $this->status : 'spare',
            ])->save();
        });
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        $query->when($term !== '', fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('make', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhere('serial_number', 'like', "%{$term}%")
                ->orWhere('asset_tag', 'like', "%{$term}%");
        }));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'category', 'make', 'model', 'serial_number', 'asset_tag',
                'status', 'condition', 'purchased_on', 'purchase_cost', 'purchase_currency',
                'supplier', 'warranty_until', 'assigned_to', 'location',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'Asset added',
                'updated' => 'Asset updated',
                'deleted' => 'Asset removed',
                'restored' => 'Asset restored',
                default => "Asset {$event}",
            });
    }
}
