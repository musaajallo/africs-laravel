<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentTotals;
use App\Support\InvoiceMeta;
use App\Support\Money;
use Brick\Math\BigDecimal;
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
    'client_id', 'project_id', 'proforma_id', 'status', 'currency', 'fx_rate',
    'issue_date', 'due_date', 'tax_label', 'tax_rate', 'notes', 'terms',
])]
class Invoice extends Model
{
    use HasDocumentTotals, HasFactory, LogsActivity, SoftDeletes;

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'draft',
        'fx_rate' => 1,
        'amount_paid' => 0,
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'fx_rate' => 'decimal:10',
            'tax_rate' => 'decimal:3',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'base_total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
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

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('position')->orderBy('id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, InvoiceMeta::EDITABLE_STATUSES, true);
    }

    /** Outstanding amount in the invoice currency. */
    public function balance(): string
    {
        return Money::sum([$this->total, '-'.$this->amount_paid], $this->currency);
    }

    /**
     * Refresh amount_paid from the allocations and move the status between
     * sent / partially_paid / paid accordingly. Draft and void are left alone.
     */
    public function recalculatePayment(): void
    {
        $paid = (string) $this->allocations()->sum('amount');
        $this->amount_paid = $paid;

        if (! in_array($this->status, ['draft', 'void'], true)) {
            $paidDec = BigDecimal::of($paid);

            $this->status = match (true) {
                $paidDec->isGreaterThanOrEqualTo($this->total) && $paidDec->isPositive() => 'paid',
                $paidDec->isPositive() => 'partially_paid',
                in_array($this->status, ['paid', 'partially_paid'], true) => 'sent',
                default => $this->status,
            };
        }

        $this->save();
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
                'status', 'currency', 'fx_rate', 'issue_date', 'due_date',
                'tax_label', 'tax_rate', 'total', 'client_id', 'project_id',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'Invoice created',
                'updated' => 'Invoice updated',
                'deleted' => 'Invoice archived',
                'restored' => 'Invoice restored',
                default => "Invoice {$event}",
            });
    }
}
