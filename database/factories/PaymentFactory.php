<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'number' => 'RCT-'.fake()->unique()->numberBetween(1000, 9999999),
            'client_id' => Client::factory(),
            'currency' => 'GMD',
            'fx_rate' => 1,
            'amount' => fake()->numberBetween(1000, 500000),
            'method' => 'Bank transfer',
            'reference' => fake()->boolean() ? fake()->bothify('TRX-####') : null,
            'paid_on' => now()->toDateString(),
        ];
    }
}
