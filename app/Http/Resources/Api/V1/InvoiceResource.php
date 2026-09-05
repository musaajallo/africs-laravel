<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'client_id' => $this->client_id,
            'project_id' => $this->project_id,
            'proforma_id' => $this->proforma_id,
            'currency' => $this->currency,
            'fx_rate' => (string) $this->fx_rate,
            'issue_date' => $this->issue_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'tax_label' => $this->tax_label,
            'tax_rate' => (string) $this->tax_rate,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'total' => $this->total,
            'base_total' => $this->base_total,
            'amount_paid' => $this->amount_paid,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'archived_at' => $this->deleted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'lines' => $this->whenLoaded('lines', fn () => $this->lines->map(fn ($line) => [
                'description' => $line->description,
                'quantity' => (string) $line->quantity,
                'unit_price' => (string) $line->unit_price,
                'line_total' => (string) $line->line_total,
            ])),
        ];
    }
}
