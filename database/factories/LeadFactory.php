<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'company' => fake()->boolean(70) ? fake()->company() : null,
            'phone' => fake()->boolean(60) ? fake()->phoneNumber() : null,
            'message' => fake()->paragraph(),
            'source' => 'website',
            'status' => 'new',
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
