<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => 'company',
            'status' => 'active',
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => 'https://'.fake()->domainName(),
            'tax_number' => fake()->numerify('TIN-######'),
            'currency' => fake()->randomElement(['GMD', 'USD', 'EUR']),
            'billing_address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => fake()->randomElement(['GM', 'SN', 'GB', 'US']),
            'notes' => null,
        ];
    }

    public function individual(): static
    {
        return $this->state(fn () => ['type' => 'individual', 'name' => fake()->name()]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
