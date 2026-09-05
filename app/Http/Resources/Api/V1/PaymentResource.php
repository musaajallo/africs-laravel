<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'client_id' => $this->client_id,
            'currency' => $this->currency,
            'fx_rate' => (string) $this->fx_rate,
            'amount' => $this->amount,
            'allocated_amount' => $this->allocated_amount,
            'method' => $this->method,
            'reference' => $this->reference,
            'paid_on' => $this->paid_on?->toDateString(),
            'notes' => $this->notes,
            'archived_at' => $this->deleted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'allocations' => $this->whenLoaded('allocations', fn () => $this->allocations->map(fn ($a) => [
                'invoice_id' => $a->invoice_id,
                'amount' => (string) $a->amount,
            ])),
        ];
    }
}
