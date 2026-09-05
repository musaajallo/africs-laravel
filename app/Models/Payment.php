<?php

namespace App\Models;

use App\Support\Money;
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
    'client_id', 'currency', 'fx_rate', 'amount', 'method', 'reference',
    'paid_on', 'notes',
])]
class Payment extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /** @var array<string, mixed> */
    protected $attributes = [
        'fx_rate' => 1,
        'allocated_amount' => 0,
    ];

    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'fx_rate' => 'decimal:10',
            'amount' => 'decimal:2',
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Amount received that has not been applied to an invoice. */
    public function unallocatedAmount(): string
    {
        return Money::sum([$this->amount, '-'.$this->allocated_amount], $this->currency);
    }

    /**
     * Replace this payment's allocations and refresh every invoice it touches,
     * old and new. Expects rows of ['invoice_id' => int, 'amount' => string].
     *
     * @param  list<array{invoice_id: int, amount: string}>  $rows
     */
    public function applyAllocations(array $rows): void
    {
        $this->getConnection()->transaction(function () use ($rows) {
            $affected = $this->allocations()->pluck('invoice_id')->all();

            $this->allocations()->delete();

            foreach ($rows as $row) {
                $this->allocations()->create([
                    'invoice_id' => $row['invoice_id'],
                    'amount' => $row['amount'],
                ]);
                $affected[] = (int) $row['invoice_id'];
            }

            $this->allocated_amount = Money::sum(array_column($rows, 'amount'), $this->currency);
            $this->save();

            Invoice::withTrashed()
                ->whereIn('id', array_unique($affected))
                ->get()
                ->each->recalculatePayment();
        });
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        $query->when($term !== '', fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('number', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%")
                ->orWhereHas('client', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"));
        }));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'currency', 'fx_rate', 'amount', 'method', 'reference', 'paid_on'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'Payment recorded',
                'updated' => 'Payment updated',
                'deleted' => 'Payment removed',
                'restored' => 'Payment restored',
                default => "Payment {$event}",
            });
    }
}
