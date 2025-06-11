<?php

namespace FilaMan\Admin\Database\Factories;

use FilaMan\Admin\Models\Plugin;
use Illuminate\Database\Eloquent\Factories\Factory;

class PluginFactory extends Factory
{
    protected $model = Plugin::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->slug(2),
            'display_name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'version' => $this->faker->randomElement(['1.0.0', '1.1.0', '2.0.0', '0.9.0']),
            'enabled' => true,
            'settings' => [],
            'metadata' => [],
            'author' => $this->faker->name(),
            'url' => $this->faker->url(),
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => false,
        ]);
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => true,
        ]);
    }

    public function withSettings(array $settings): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => $settings,
        ]);
    }

    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }

    public function core(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'admin',
            'display_name' => 'Admin Panel',
            'description' => 'Core admin panel plugin',
        ]);
    }
}