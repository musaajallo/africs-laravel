<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'make' => $this->make,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'asset_tag' => $this->asset_tag,
            'status' => $this->status,
            'condition' => $this->condition,
            'purchased_on' => $this->purchased_on?->toDateString(),
            'purchase_cost' => $this->purchase_cost,
            'purchase_currency' => $this->purchase_currency,
            'supplier' => $this->supplier,
            'warranty_until' => $this->warranty_until?->toDateString(),
            'assigned_to' => $this->assigned_to,
            'assigned_on' => $this->assigned_on?->toDateString(),
            'location' => $this->location,
            'notes' => $this->notes,
            'archived_at' => $this->deleted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
