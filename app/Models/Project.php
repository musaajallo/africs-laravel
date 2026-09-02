<?php

namespace App\Models;

use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'client_id', 'name', 'service_line', 'status', 'description',
    'starts_on', 'ends_on', 'budget_amount', 'budget_currency', 'owner_id',
])]
class Project extends Model
{
    use HasFactory, HasTags, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'budget_amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role')
            ->orderBy('name');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client_id', 'name', 'service_line', 'status', 'description',
                'starts_on', 'ends_on', 'budget_amount', 'budget_currency', 'owner_id',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'Project created',
                'updated' => 'Project updated',
                'deleted' => 'Project archived',
                'restored' => 'Project restored',
                default => "Project {$event}",
            });
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        $query->when($term !== '', fn (Builder $q) => $q->where('name', 'like', "%{$term}%"));
    }
}
