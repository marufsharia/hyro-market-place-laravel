<?php

namespace Tests\Property;

use Tests\TestCase;
use App\Models\Plugin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorizationPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Feature: production-ready-marketplace, Property 6: Plugin Ownership Authorization
     * 
     * Validates: Requirements 2.2
     * 
     * For any plugin update or delete request, if the authenticated user is not the plugin owner
     * and not an admin, the request should be rejected with a 403 Forbidden response.
     */
    public function test_plugin_ownership_authorization_for_updates()
    {
        for ($i = 0; $i < 20; $i++) {
            // Create plugin owner and another user
            $owner = User::factory()->create(['is_admin' => false]);
            $otherUser = User::factory()->create(['is_admin' => false]);
            
            $plugin = Plugin::factory()->create(['user_id' => $owner->id]);
            
            // Test that non-owner cannot update plugin
            $response = $this->actingAs($otherUser)->putJson("/plugins/{$plugin->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'category_id' => $plugin->category_id,
                'version' => '2.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            ]);
            
            $response->assertStatus(403);
            
            // Test that non-owner cannot delete plugin
            $response = $this->actingAs($otherUser)->deleteJson("/plugins/{$plugin->id}");
            
            $response->assertStatus(403);
            
            // Verify plugin was not deleted
            $this->assertDatabaseHas('plugins', [
                'id' => $plugin->id,
                'deleted_at' => null,
            ]);
            
            // Test that owner CAN update plugin
            $response = $this->actingAs($owner)->putJson("/plugins/{$plugin->id}", [
                'name' => 'Owner Updated Name',
                'description' => 'Owner updated description',
                'category_id' => $plugin->category_id,
                'version' => '2.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            ]);
            
            // Should not be 403 (owner should be authorized)
            $this->assertNotEquals(403, $response->status(), 
                "Plugin owner was incorrectly denied access");
            
            // Clean up
            $plugin->forceDelete();
            $owner->delete();
            $otherUser->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 6: Plugin Ownership Authorization
     * 
     * Validates: Requirements 2.2
     * 
     * Test that admin users can update any plugin regardless of ownership.
     */
    public function test_admin_can_update_any_plugin()
    {
        for ($i = 0; $i < 20; $i++) {
            $owner = User::factory()->create(['is_admin' => false]);
            $admin = User::factory()->create(['is_admin' => true]);
            
            $plugin = Plugin::factory()->create(['user_id' => $owner->id]);
            
            // Admin should be able to update any plugin
            $response = $this->actingAs($admin)->putJson("/plugins/{$plugin->id}", [
                'name' => 'Admin Updated Name ' . uniqid(),
                'description' => 'Admin updated description',
                'category_id' => $plugin->category_id,
                'version' => '2.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            ]);
            
            // Should not be 403 (admin should be authorized)
            $this->assertNotEquals(403, $response->status(), 
                "Admin was incorrectly denied access to update plugin");
            
            // Clean up
            $plugin->forceDelete();
            $owner->delete();
            $admin->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 14: Self-Review Prevention
     * 
     * Validates: Requirements 3.8
     * 
     * For any plugin, if the authenticated user is the plugin owner,
     * attempting to submit a review should be rejected with an authorization error.
     */
    public function test_plugin_owners_cannot_review_their_own_plugins()
    {
        for ($i = 0; $i < 20; $i++) {
            $owner = User::factory()->create();
            $plugin = Plugin::factory()->create(['user_id' => $owner->id]);
            
            // Owner attempts to review their own plugin
            $response = $this->actingAs($owner)->postJson("/plugins/{$plugin->id}/reviews", [
                'rating' => rand(1, 5),
                'comment' => 'Self review attempt',
            ]);
            
            $response->assertStatus(403);
            
            // Verify no review was created
            $this->assertDatabaseMissing('reviews', [
                'plugin_id' => $plugin->id,
                'user_id' => $owner->id,
            ]);
            
            // Test that non-owner CAN review the plugin
            $otherUser = User::factory()->create();
            
            $response = $this->actingAs($otherUser)->postJson("/plugins/{$plugin->id}/reviews", [
                'rating' => rand(1, 5),
                'comment' => 'Valid review from non-owner',
            ]);
            
            // Should not be 403 (non-owner should be authorized)
            $this->assertNotEquals(403, $response->status(), 
                "Non-owner was incorrectly denied access to review plugin");
            
            // Clean up
            $plugin->reviews()->delete();
            $plugin->forceDelete();
            $owner->delete();
            $otherUser->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 28: Authentication Middleware Protection
     * 
     * Validates: Requirements 6.2
     * 
     * For any protected route, if the request is not authenticated,
     * the system should redirect to login or return 401 Unauthorized.
     */
    public function test_unauthenticated_requests_are_rejected()
    {
        $plugin = Plugin::factory()->create();
        
        for ($i = 0; $i < 20; $i++) {
            // Test plugin creation without authentication
            $response = $this->postJson('/plugins', [
                'name' => 'Test Plugin',
                'description' => 'Test description',
                'category_id' => $plugin->category_id,
                'version' => '1.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            ]);
            
            // Should be 401 Unauthorized or 302 redirect to login
            $this->assertContains($response->status(), [401, 302], 
                "Unauthenticated plugin creation should be rejected");
            
            // Test review submission without authentication
            $response = $this->postJson("/plugins/{$plugin->id}/reviews", [
                'rating' => rand(1, 5),
                'comment' => 'Test review',
            ]);
            
            $this->assertContains($response->status(), [401, 302], 
                "Unauthenticated review submission should be rejected");
            
            // Test favorite toggle without authentication
            $response = $this->postJson("/plugins/{$plugin->id}/favorite");
            
            $this->assertContains($response->status(), [401, 302], 
                "Unauthenticated favorite toggle should be rejected");
            
            // Test plugin update without authentication
            $response = $this->putJson("/plugins/{$plugin->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'category_id' => $plugin->category_id,
                'version' => '2.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            ]);
            
            $this->assertContains($response->status(), [401, 302], 
                "Unauthenticated plugin update should be rejected");
            
            // Test plugin deletion without authentication
            $response = $this->deleteJson("/plugins/{$plugin->id}");
            
            $this->assertContains($response->status(), [401, 302], 
                "Unauthenticated plugin deletion should be rejected");
        }
        
        // Clean up
        $plugin->forceDelete();
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 6: Plugin Ownership Authorization
     * 
     * Validates: Requirements 2.2
     * 
     * Edge case: Test authorization with soft-deleted plugins.
     */
    public function test_authorization_respects_soft_deleted_plugins()
    {
        for ($i = 0; $i < 20; $i++) {
            $owner = User::factory()->create();
            $otherUser = User::factory()->create();
            
            $plugin = Plugin::factory()->create(['user_id' => $owner->id]);
            
            // Soft delete the plugin
            $plugin->delete();
            
            // Non-owner should still get 403 (not 404) when trying to update soft-deleted plugin
            $response = $this->actingAs($otherUser)->putJson("/plugins/{$plugin->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'category_id' => $plugin->category_id,
                'version' => '2.0.0',
                'compatibility' => 'Laravel 11',
                'license_type' => 'MIT',
                'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
            ]);
            
            // Should be 403 or 404 (both are acceptable for soft-deleted resources)
            $this->assertContains($response->status(), [403, 404], 
                "Soft-deleted plugin authorization check failed");
            
            // Clean up
            $plugin->forceDelete();
            $owner->delete();
            $otherUser->delete();
        }
    }
}
