<?php

namespace Database\Factories;

use App\Models\Client;
use App\Support\ClientTypes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(ClientTypes::TYPES);
        $categories = ClientTypes::categoriesFor($type);

        return [
            'name' => fake()->company(),
            'type' => $type,
            'category' => $categories ? fake()->randomElement($categories) : null,
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
        return $this->state(fn () => [
            'type' => 'individual',
            'category' => null,
            'name' => fake()->name(),
        ]);
    }

    public function government(): static
    {
        return $this->state(fn () => [
            'type' => 'government',
            'category' => fake()->randomElement(ClientTypes::categoriesFor('government')),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
