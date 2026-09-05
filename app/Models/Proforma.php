<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentTotals;
use App\Support\ProformaMeta;
use App\Support\Sequence;
use App\Support\Settings;
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

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'draft',
        'fx_rate' => 1,
    ];

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

    /** Any proforma can become an invoice, once, regardless of its status. */
    public function canBeConverted(): bool
    {
        return ! $this->isConverted() && $this->converted_invoice_id === null;
    }

    /**
     * Create a draft invoice from this proforma, copying the client, project,
     * currency, snapshotted FX rate, tax and line items, and record a two-way
     * link. The proforma moves to the "converted" status.
     */
    public function convertToInvoice(?int $userId = null): Invoice
    {
        return $this->getConnection()->transaction(function () use ($userId) {
            $invoice = new Invoice([
                'client_id' => $this->client_id,
                'project_id' => $this->project_id,
                'proforma_id' => $this->id,
                'status' => 'draft',
                'currency' => $this->currency,
                'fx_rate' => $this->fx_rate,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(Settings::paymentTermsDays())->toDateString(),
                'tax_label' => $this->tax_label,
                'tax_rate' => $this->tax_rate,
                'notes' => $this->notes,
                'terms' => $this->terms,
            ]);
            $invoice->number = Sequence::next('invoice', 'INV');
            $invoice->created_by = $userId;
            $invoice->save();

            foreach ($this->lines as $line) {
                $invoice->lines()->create([
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'position' => $line->position,
                ]);
            }

            $invoice->load('lines');
            $invoice->saveWithTotals();

            $this->forceFill([
                'status' => 'converted',
                'converted_invoice_id' => $invoice->id,
            ])->save();

            return $invoice;
        });
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
