<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentationCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(2, true);
        
        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 1000),
            'description' => fake()->sentence(12),
            'icon' => fake()->randomElement(['📚', '🔧', '⚙️', '🚀', '💡', '🎯', '📊', '🔒', '⚡', '🌟']),
            'order' => fake()->numberBetween(1, 100),
        ];
    }
}
