<?php

namespace Tests\Property;

use Tests\TestCase;
use App\Models\Plugin;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;

class FavoriteConstraintsPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Feature: production-ready-marketplace, Property 3: Duplicate Favorite Prevention
     * 
     * Validates: Requirements 1.6
     * 
     * For any user and plugin combination, attempting to create a favorite when one already exists
     * should either prevent the duplicate or be idempotent (no duplicate created).
     */
    public function test_duplicate_favorites_are_prevented_for_all_user_plugin_combinations()
    {
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create();
            $plugin = Plugin::factory()->create();
            
            // Create first favorite
            $favorite1 = Favorite::create([
                'user_id' => $user->id,
                'plugin_id' => $plugin->id,
            ]);
            
            $this->assertDatabaseHas('favorites', [
                'user_id' => $user->id,
                'plugin_id' => $plugin->id,
            ]);
            
            // Attempt to create duplicate
            $duplicateCreated = false;
            try {
                Favorite::create([
                    'user_id' => $user->id,
                    'plugin_id' => $plugin->id,
                ]);
                $duplicateCreated = true;
            } catch (QueryException $e) {
                // Database constraint prevented duplicate - this is correct behavior
                $this->assertStringContainsString('unique', strtolower($e->getMessage()));
            }
            
            // Verify only one favorite exists regardless of whether exception was thrown
            $count = Favorite::where('user_id', $user->id)
                ->where('plugin_id', $plugin->id)
                ->count();
            
            $this->assertEquals(
                1, 
                $count, 
                "Duplicate favorite was created in iteration {$i}"
            );
            
            // Clean up
            $favorite1->delete();
            $plugin->delete();
            $user->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 3: Duplicate Favorite Prevention
     * 
     * Validates: Requirements 1.6
     * 
     * Edge case: User can favorite multiple different plugins.
     */
    public function test_user_can_favorite_multiple_different_plugins()
    {
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create();
            $pluginCount = rand(2, 10);
            $plugins = Plugin::factory()->count($pluginCount)->create();
            
            // Create favorites for all plugins
            foreach ($plugins as $plugin) {
                Favorite::create([
                    'user_id' => $user->id,
                    'plugin_id' => $plugin->id,
                ]);
            }
            
            // Verify all favorites were created
            $favoriteCount = Favorite::where('user_id', $user->id)->count();
            $this->assertEquals(
                $pluginCount, 
                $favoriteCount,
                "User should be able to favorite {$pluginCount} different plugins in iteration {$i}"
            );
            
            // Clean up
            Favorite::where('user_id', $user->id)->delete();
            $plugins->each->delete();
            $user->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 3: Duplicate Favorite Prevention
     * 
     * Validates: Requirements 1.6
     * 
     * Edge case: Multiple users can favorite the same plugin.
     */
    public function test_multiple_users_can_favorite_same_plugin()
    {
        for ($i = 0; $i < 20; $i++) {
            $plugin = Plugin::factory()->create();
            $userCount = rand(2, 10);
            $users = User::factory()->count($userCount)->create();
            
            // Create favorites from all users
            foreach ($users as $user) {
                Favorite::create([
                    'user_id' => $user->id,
                    'plugin_id' => $plugin->id,
                ]);
            }
            
            // Verify all favorites were created
            $favoriteCount = Favorite::where('plugin_id', $plugin->id)->count();
            $this->assertEquals(
                $userCount, 
                $favoriteCount,
                "{$userCount} users should be able to favorite the same plugin in iteration {$i}"
            );
            
            // Clean up
            Favorite::where('plugin_id', $plugin->id)->delete();
            $users->each->delete();
            $plugin->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 19: Cascade Delete Favorites
     * 
     * Validates: Requirements 4.7
     * 
     * For any plugin with associated favorites, when the plugin is deleted,
     * all favorite records referencing that plugin should also be deleted.
     * 
     * Note: This tests force delete since soft delete doesn't trigger DB cascade.
     * In production, we should handle this via model events or explicit deletion.
     */
    public function test_favorites_are_cascade_deleted_when_plugin_is_force_deleted()
    {
        for ($i = 0; $i < 20; $i++) {
            $plugin = Plugin::factory()->create();
            $userCount = rand(1, 10);
            $users = User::factory()->count($userCount)->create();
            
            // Create favorites
            foreach ($users as $user) {
                Favorite::create([
                    'user_id' => $user->id,
                    'plugin_id' => $plugin->id,
                ]);
            }
            
            $favoriteCount = Favorite::where('plugin_id', $plugin->id)->count();
            $this->assertEquals($userCount, $favoriteCount);
            
            // Force delete the plugin (bypasses soft delete)
            $plugin->forceDelete();
            
            // Verify all favorites were cascade deleted
            $remainingFavorites = Favorite::where('plugin_id', $plugin->id)->count();
            $this->assertEquals(
                0, 
                $remainingFavorites,
                "All favorites should be cascade deleted when plugin is force deleted in iteration {$i}"
            );
            
            // Clean up
            $users->each->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 19: Cascade Delete Favorites
     * 
     * Validates: Requirements 4.7
     * 
     * When a plugin is soft deleted, favorites should be explicitly deleted.
     */
    public function test_favorites_are_deleted_when_plugin_is_soft_deleted()
    {
        for ($i = 0; $i < 20; $i++) {
            $plugin = Plugin::factory()->create();
            $userCount = rand(1, 10);
            $users = User::factory()->count($userCount)->create();
            
            // Create favorites
            foreach ($users as $user) {
                Favorite::create([
                    'user_id' => $user->id,
                    'plugin_id' => $plugin->id,
                ]);
            }
            
            $favoriteCount = Favorite::where('plugin_id', $plugin->id)->count();
            $this->assertEquals($userCount, $favoriteCount);
            
            // Soft delete the plugin
            $plugin->delete();
            
            // Verify all favorites were deleted
            $remainingFavorites = Favorite::where('plugin_id', $plugin->id)->count();
            $this->assertEquals(
                0, 
                $remainingFavorites,
                "All favorites should be deleted when plugin is soft deleted in iteration {$i}"
            );
            
            // Clean up
            $users->each->delete();
            $plugin->forceDelete();
        }
    }
}
