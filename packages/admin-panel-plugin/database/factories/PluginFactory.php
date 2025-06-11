<?php

namespace FilaMan\AdminPanelPlugin\Database\Factories;

use FilaMan\AdminPanelPlugin\Models\Plugin;
use Illuminate\Database\Eloquent\Factories\Factory;

class PluginFactory extends Factory
{
    protected $model = Plugin::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word().'-plugin';

        return [
            'name' => $name,
            'display_name' => ucwords(str_replace('-', ' ', $name)),
            'description' => $this->faker->sentence(),
            'version' => $this->faker->randomElement(['1.0.0', '1.1.0', '2.0.0', '0.1.0']),
            'enabled' => $this->faker->boolean(80), // 80% chance of being enabled
            'settings' => [],
            'metadata' => [
                'created' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
                'downloads' => $this->faker->numberBetween(0, 10000),
            ],
            'author' => $this->faker->name(),
            'url' => $this->faker->url(),
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => false,
        ]);
    }

    public function withSettings(array $settings): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => $settings,
        ]);
    }
}
