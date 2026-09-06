<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'folder_id', 'related_subscription_id', 'title', 'username', 'password',
    'url', 'notes', 'totp_secret', 'custom_fields',
])]
class VaultEntry extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'notes' => 'encrypted',
            'totp_secret' => 'encrypted',
            'custom_fields' => 'encrypted:array',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(VaultFolder::class, 'folder_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Non-secret metadata is fine to log; encrypted fields never are. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['folder_id', 'title', 'username', 'url', 'related_subscription_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'Vault entry added',
                'updated' => 'Vault entry updated',
                'deleted' => 'Vault entry deleted',
                'restored' => 'Vault entry restored',
                default => "Vault entry {$event}",
            });
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        $query->when($term !== '', fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%")
                ->orWhere('url', 'like', "%{$term}%");
        }));
    }
}
