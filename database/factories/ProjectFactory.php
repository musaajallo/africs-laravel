<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Support\ProjectMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => fake()->catchPhrase(),
            'service_line' => fake()->randomElement(ProjectMeta::serviceLineKeys()),
            'status' => fake()->randomElement(['proposed', 'active', 'on_hold', 'completed']),
            'description' => fake()->boolean(70) ? fake()->paragraph() : null,
            'starts_on' => fake()->boolean(70) ? fake()->dateTimeBetween('-3 months', '+1 month')->format('Y-m-d') : null,
            'ends_on' => fake()->boolean(50) ? fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d') : null,
            'budget_amount' => fake()->boolean(60) ? fake()->numberBetween(50000, 3000000) : null,
            'budget_currency' => 'GMD',
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
