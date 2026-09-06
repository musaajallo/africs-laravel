<?php

namespace Database\Factories;

use App\Models\VaultEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VaultEntry>
 */
class VaultEntryFactory extends Factory
{
    protected $model = VaultEntry::class;

    public function definition(): array
    {
        return [
            'title' => fake()->company().' '.fake()->randomElement(['cPanel', 'Admin', 'SSH', 'DNS', 'SMTP']),
            'username' => fake()->userName(),
            'password' => fake()->password(14, 20),
            'url' => 'https://'.fake()->domainName(),
            'notes' => fake()->boolean(40) ? fake()->sentence() : null,
        ];
    }
}
