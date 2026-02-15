<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Plugin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'plugin_id' => Plugin::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional(0.8)->paragraph(),
        ];
    }

    public function withRating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $rating,
        ]);
    }
}
