<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Support\AssetMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $category = fake()->randomElement(AssetMeta::categoryKeys());

        return [
            'name' => ucfirst($category).' — '.fake()->bothify('##??'),
            'category' => $category,
            'make' => fake()->randomElement(['Dell', 'HP', 'Apple', 'Lenovo', 'Canon']),
            'model' => fake()->bothify('Model ###'),
            'serial_number' => fake()->unique()->bothify('SN-########'),
            'asset_tag' => fake()->unique()->bothify('AF-####'),
            'status' => fake()->randomElement(['in_use', 'spare', 'repair']),
            'condition' => fake()->randomElement(AssetMeta::conditionKeys()),
            'purchased_on' => fake()->boolean(80) ? fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d') : null,
            'purchase_cost' => fake()->boolean(80) ? fake()->numberBetween(5000, 90000) : null,
            'purchase_currency' => 'GMD',
            'location' => fake()->randomElement(['Head office', 'Serekunda office', 'Remote', null]),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
