<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'base_currency' => $this->base_currency,
            'quote_currency' => $this->quote_currency,
            // Value of 1 unit of quote_currency in base_currency.
            'rate' => (string) $this->rate,
            'rate_date' => $this->rate_date->toDateString(),
            'source' => $this->source,
        ];
    }
}
