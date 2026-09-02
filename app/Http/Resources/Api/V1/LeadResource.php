<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'channel' => $this->source,
            'referred_by_client_id' => $this->referred_by_client_id,
            'referral_source' => $this->referral_source,
            'status' => $this->status,
            'owner_id' => $this->owner_id,
            'converted_client_id' => $this->converted_client_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
