<?php

namespace Tests\Property;

use Tests\TestCase;
use App\Models\Plugin;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RatingCalculationPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Feature: production-ready-marketplace, Property 1: Rating Recalculation on Review Changes
     * 
     * Validates: Requirements 1.3, 1.4
     * 
     * For any plugin with reviews, when a review is created, updated, or deleted,
     * the plugin's average rating and review count should accurately reflect all current reviews.
     */
    public function test_rating_recalculates_correctly_for_all_review_operations()
    {
        for ($i = 0; $i < 20; $i++) {
            // Create a fresh plugin for each iteration
            $plugin = Plugin::factory()->create([
                'rating_avg' => 0.00,
                'rating_count' => 0,
            ]);
            
            $reviewCount = rand(3, 20); // At least 3 reviews for testing
            $users = User::factory()->count($reviewCount)->create();
            
            // Create random reviews
            foreach ($users as $user) {
                Review::factory()->create([
                    'plugin_id' => $plugin->id,
                    'user_id' => $user->id,
                    'rating' => rand(1, 5),
                ]);
            }
            
            // Verify rating after creation
            $plugin->refresh();
            $expectedAvg = round($plugin->reviews()->avg('rating'), 2);
            $expectedCount = $plugin->reviews()->count();
            
            $this->assertEquals(
                $expectedAvg, 
                (float) $plugin->rating_avg,
                "Rating average mismatch after creation in iteration {$i}",
                0.01
            );
            $this->assertEquals(
                $expectedCount, 
                $plugin->rating_count,
                "Rating count mismatch after creation in iteration {$i}"
            );
            
            // Update a random review
            $reviewToUpdate = $plugin->reviews()->inRandomOrder()->first();
            $newRating = rand(1, 5);
            $reviewToUpdate->update(['rating' => $newRating]);
            
            $plugin->refresh();
            $expectedAvg = round($plugin->reviews()->avg('rating'), 2);
            
            $this->assertEquals(
                $expectedAvg, 
                (float) $plugin->rating_avg,
                "Rating average mismatch after update in iteration {$i}",
                0.01
            );
            
            // Delete a random review
            $reviewToDelete = $plugin->reviews()->inRandomOrder()->first();
            $reviewToDelete->delete();
            
            $plugin->refresh();
            $expectedCount = $plugin->reviews()->count();
            $expectedAvg = $expectedCount > 0 
                ? round($plugin->reviews()->avg('rating'), 2) 
                : 0.00;
            
            $this->assertEquals(
                $expectedAvg, 
                (float) $plugin->rating_avg,
                "Rating average mismatch after delete in iteration {$i}",
                0.01
            );
            $this->assertEquals(
                $expectedCount, 
                $plugin->rating_count,
                "Rating count mismatch after delete in iteration {$i}"
            );
            
            // Clean up for next iteration
            $plugin->reviews->each->delete();
            $plugin->delete();
            $users->each->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 1: Rating Recalculation on Review Changes
     * 
     * Validates: Requirements 1.3, 1.4
     * 
     * Edge case: When all reviews are deleted, rating should be 0.
     */
    public function test_rating_resets_to_zero_when_all_reviews_deleted()
    {
        for ($i = 0; $i < 20; $i++) {
            $plugin = Plugin::factory()->create();
            $reviewCount = rand(1, 10);
            
            $reviews = Review::factory()->count($reviewCount)->create([
                'plugin_id' => $plugin->id,
                'rating' => rand(1, 5),
            ]);
            
            $plugin->refresh();
            $this->assertGreaterThan(0, $plugin->rating_avg);
            $this->assertEquals($reviewCount, $plugin->rating_count);
            
            // Delete all reviews one by one to trigger events
            $plugin->reviews->each(function ($review) {
                $review->delete();
            });
            
            $plugin->refresh();
            $this->assertEquals(0.00, (float) $plugin->rating_avg);
            $this->assertEquals(0, $plugin->rating_count);
            
            // Clean up
            $plugin->delete();
        }
    }
}
