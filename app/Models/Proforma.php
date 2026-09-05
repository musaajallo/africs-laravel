<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentTotals;
use App\Support\ProformaMeta;
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
    'client_id', 'project_id', 'status', 'currency', 'fx_rate', 'issue_date',
    'valid_until', 'tax_label', 'tax_rate', 'notes', 'terms',
])]
class Proforma extends Model
{
    use HasDocumentTotals, HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'valid_until' => 'date',
            'fx_rate' => 'decimal:10',
            'tax_rate' => 'decimal:3',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'base_total' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ProformaLine::class)->orderBy('position')->orderBy('id');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ProformaMeta::EDITABLE_STATUSES, true);
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        $query->when($term !== '', fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('number', 'like', "%{$term}%")
                ->orWhereHas('client', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"));
        }));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status', 'currency', 'fx_rate', 'issue_date', 'valid_until',
                'tax_label', 'tax_rate', 'total', 'client_id', 'project_id',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'Proforma created',
                'updated' => 'Proforma updated',
                'deleted' => 'Proforma archived',
                'restored' => 'Proforma restored',
                default => "Proforma {$event}",
            });
    }
}
