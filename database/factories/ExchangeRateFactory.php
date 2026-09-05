<?php

namespace Database\Factories;

use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    public function definition(): array
    {
        return [
            'base_currency' => 'GMD',
            'quote_currency' => fake()->randomElement(['USD', 'EUR']),
            'rate' => fake()->randomFloat(4, 60, 90),
            'rate_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'source' => 'manual',
        ];
    }

    public function for_currency(string $currency): static
    {
        return $this->state(fn () => ['quote_currency' => $currency]);
    }

    public function on(string $date): static
    {
        return $this->state(fn () => ['rate_date' => $date]);
    }
}
