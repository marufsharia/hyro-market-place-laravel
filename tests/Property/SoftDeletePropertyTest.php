<?php

namespace Tests\Property;

use Tests\TestCase;
use App\Models\Plugin;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SoftDeletePropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Feature: production-ready-marketplace, Property 4: Soft Delete Preservation
     * 
     * Validates: Requirements 1.7
     * 
     * For any plugin that is deleted, the plugin record should remain in the database
     * with a deleted_at timestamp, and should not appear in normal queries but should
     * be retrievable with soft delete queries.
     */
    public function test_soft_deleted_plugins_remain_in_database_with_timestamp()
    {
        for ($i = 0; $i < 20; $i++) {
            // Create a plugin with random data
            $user = User::factory()->create();
            $category = Category::factory()->create();
            
            $plugin = Plugin::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => 'Test Plugin ' . $i . ' ' . uniqid(),
            ]);
            
            $pluginId = $plugin->id;
            $pluginName = $plugin->name;
            
            // Verify plugin exists in normal queries
            $this->assertDatabaseHas('plugins', [
                'id' => $pluginId,
                'name' => $pluginName,
            ]);
            
            $this->assertNotNull(Plugin::find($pluginId));
            
            // Soft delete the plugin
            $plugin->delete();
            
            // Verify plugin has deleted_at timestamp
            $this->assertDatabaseHas('plugins', [
                'id' => $pluginId,
                'name' => $pluginName,
            ]);
            
            // Verify deleted_at is not null
            $deletedPlugin = Plugin::withTrashed()->find($pluginId);
            $this->assertNotNull($deletedPlugin);
            $this->assertNotNull($deletedPlugin->deleted_at);
            
            // Verify plugin does NOT appear in normal queries
            $this->assertNull(Plugin::find($pluginId));
            $this->assertEquals(0, Plugin::where('id', $pluginId)->count());
            
            // Verify plugin IS retrievable with withTrashed()
            $this->assertNotNull(Plugin::withTrashed()->find($pluginId));
            $this->assertEquals(1, Plugin::withTrashed()->where('id', $pluginId)->count());
            
            // Verify plugin IS retrievable with onlyTrashed()
            $this->assertNotNull(Plugin::onlyTrashed()->find($pluginId));
            $this->assertEquals(1, Plugin::onlyTrashed()->where('id', $pluginId)->count());
            
            // Clean up
            $plugin->forceDelete();
            $user->delete();
            $category->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 4: Soft Delete Preservation
     * 
     * Validates: Requirements 1.7
     * 
     * Edge case: Soft deleted plugins can be restored.
     */
    public function test_soft_deleted_plugins_can_be_restored()
    {
        for ($i = 0; $i < 20; $i++) {
            $plugin = Plugin::factory()->create([
                'name' => 'Restorable Plugin ' . $i . ' ' . uniqid(),
            ]);
            
            $pluginId = $plugin->id;
            
            // Soft delete
            $plugin->delete();
            
            // Verify it's deleted
            $this->assertNull(Plugin::find($pluginId));
            $this->assertNotNull(Plugin::withTrashed()->find($pluginId));
            
            // Restore the plugin
            $deletedPlugin = Plugin::withTrashed()->find($pluginId);
            $deletedPlugin->restore();
            
            // Verify it's restored
            $restoredPlugin = Plugin::find($pluginId);
            $this->assertNotNull($restoredPlugin);
            $this->assertNull($restoredPlugin->deleted_at);
            
            // Verify it appears in normal queries again
            $this->assertEquals(1, Plugin::where('id', $pluginId)->count());
            
            // Clean up
            $plugin->forceDelete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 4: Soft Delete Preservation
     * 
     * Validates: Requirements 1.7
     * 
     * Edge case: Force delete permanently removes the plugin.
     */
    public function test_force_delete_permanently_removes_plugin()
    {
        for ($i = 0; $i < 20; $i++) {
            $plugin = Plugin::factory()->create([
                'name' => 'Force Delete Plugin ' . $i . ' ' . uniqid(),
            ]);
            
            $pluginId = $plugin->id;
            
            // Force delete (permanent)
            $plugin->forceDelete();
            
            // Verify plugin is completely gone
            $this->assertNull(Plugin::find($pluginId));
            $this->assertNull(Plugin::withTrashed()->find($pluginId));
            $this->assertNull(Plugin::onlyTrashed()->find($pluginId));
            
            // Verify no record in database
            $this->assertDatabaseMissing('plugins', [
                'id' => $pluginId,
            ]);
        }
    }
}
