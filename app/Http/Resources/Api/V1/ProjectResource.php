<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'client_id' => $this->client_id,
            'service_line' => $this->service_line,
            'status' => $this->status,
            'description' => $this->description,
            'starts_on' => $this->starts_on?->toDateString(),
            'ends_on' => $this->ends_on?->toDateString(),
            'budget_amount' => $this->budget_amount,
            'budget_currency' => $this->budget_currency,
            'owner_id' => $this->owner_id,
            'archived_at' => $this->deleted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'members' => $this->whenLoaded('members', fn () => $this->members->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->pivot->role,
            ])),
        ];
    }
}
