<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'category' => $this->category,
            'status' => $this->status,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'tax_number' => $this->tax_number,
            'currency' => $this->currency,
            'billing_address' => $this->billing_address,
            'city' => $this->city,
            'country' => $this->country,
            'notes' => $this->notes,
            'owner_id' => $this->owner_id,
            'archived_at' => $this->deleted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
        ];
    }
}
