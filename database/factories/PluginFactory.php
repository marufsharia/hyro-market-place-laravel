<?php

namespace Database\Factories;

use App\Models\Plugin;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PluginFactory extends Factory
{
    protected $model = Plugin::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(3, true),
            'logo_path' => null,
            'version' => fake()->numerify('#.#.#'),
            'status' => fake()->randomElement(['pending', 'active', 'inactive', 'rejected']),
            'compatibility' => 'Laravel ' . fake()->randomElement(['10', '11']),
            'requirements' => [
                'php' => fake()->randomElement(['8.1', '8.2', '8.3']),
                'laravel' => fake()->randomElement(['10.0', '11.0']),
            ],
            'license_type' => fake()->randomElement(['MIT', 'GPL', 'Apache', 'Proprietary']),
            'downloads' => fake()->numberBetween(0, 10000),
            'rating_avg' => 0.00,
            'rating_count' => 0,
            'published_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'published_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'published_at' => null,
        ]);
    }

    public function withRatings(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating_avg' => fake()->randomFloat(2, 1, 5),
            'rating_count' => fake()->numberBetween(1, 100),
        ]);
    }
}
