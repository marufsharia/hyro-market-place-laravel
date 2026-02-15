<?php

namespace Tests\Property;

use Tests\TestCase;
use App\Models\Plugin;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SlugGenerationPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Feature: production-ready-marketplace, Property 8: Unique Slug Generation
     * 
     * Validates: Requirements 2.5
     * 
     * For any plugin name, the system should generate a unique slug, and if a slug
     * collision occurs, the system should append a suffix to ensure uniqueness.
     */
    public function test_slug_is_automatically_generated_from_name()
    {
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create();
            $category = Category::factory()->create();
            
            // Generate random plugin names
            $names = [
                'My Awesome Plugin',
                'Test Plugin 123',
                'Super-Cool_Plugin',
                'Plugin with CAPS',
                'Plugin   with   spaces',
                'Plugin-with-dashes',
                'Plugin_with_underscores',
                'Ñoño Plugin',
                'Plugin & More',
                'Plugin @ Home',
            ];
            
            $name = $names[array_rand($names)] . ' ' . uniqid();
            
            $plugin = Plugin::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => $name,
                'slug' => '', // Empty slug to trigger auto-generation
            ]);
            
            // Verify slug was generated
            $this->assertNotEmpty($plugin->slug);
            
            // Verify slug is URL-friendly (lowercase, no spaces, no special chars except dashes)
            $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $plugin->slug);
            
            // Verify slug is not empty and contains alphanumeric characters
            $this->assertNotEmpty($plugin->slug);
            $this->assertGreaterThan(0, preg_match('/[a-z0-9]/', $plugin->slug));
            
            // Clean up
            $plugin->delete();
            $user->delete();
            $category->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 8: Unique Slug Generation
     * 
     * Validates: Requirements 2.5
     * 
     * When a slug collision occurs, the system should append a numeric suffix.
     */
    public function test_slug_collision_appends_numeric_suffix()
    {
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create();
            $category = Category::factory()->create();
            
            $baseName = 'Duplicate Plugin ' . uniqid();
            
            // Create first plugin with this name
            $plugin1 = Plugin::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => $baseName,
                'slug' => '', // Trigger auto-generation
            ]);
            
            $firstSlug = $plugin1->slug;
            $this->assertNotEmpty($firstSlug);
            
            // Create second plugin with the same name
            $plugin2 = Plugin::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => $baseName,
                'slug' => '', // Trigger auto-generation
            ]);
            
            $secondSlug = $plugin2->slug;
            $this->assertNotEmpty($secondSlug);
            
            // Verify slugs are different
            $this->assertNotEquals($firstSlug, $secondSlug);
            
            // Verify second slug has numeric suffix
            $this->assertMatchesRegularExpression('/\-\d+$/', $secondSlug);
            
            // Create third plugin with the same name
            $plugin3 = Plugin::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => $baseName,
                'slug' => '', // Trigger auto-generation
            ]);
            
            $thirdSlug = $plugin3->slug;
            $this->assertNotEmpty($thirdSlug);
            
            // Verify all three slugs are unique
            $this->assertNotEquals($firstSlug, $thirdSlug);
            $this->assertNotEquals($secondSlug, $thirdSlug);
            
            // Verify third slug has numeric suffix
            $this->assertMatchesRegularExpression('/\-\d+$/', $thirdSlug);
            
            // Verify all slugs are unique in database
            $slugs = [$firstSlug, $secondSlug, $thirdSlug];
            $this->assertEquals(3, count(array_unique($slugs)));
            
            // Clean up
            $plugin1->delete();
            $plugin2->delete();
            $plugin3->delete();
            $user->delete();
            $category->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 8: Unique Slug Generation
     * 
     * Validates: Requirements 2.5
     * 
     * Edge case: Manually provided slugs should not be overwritten.
     */
    public function test_manually_provided_slug_is_not_overwritten()
    {
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create();
            $category = Category::factory()->create();
            
            $customSlug = 'custom-slug-' . uniqid();
            
            $plugin = Plugin::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => 'Plugin with Custom Slug ' . $i,
                'slug' => $customSlug, // Manually provided slug
            ]);
            
            // Verify the custom slug was preserved
            $this->assertEquals($customSlug, $plugin->slug);
            
            // Clean up
            $plugin->delete();
            $user->delete();
            $category->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 8: Unique Slug Generation
     * 
     * Validates: Requirements 2.5
     * 
     * Edge case: Slug generation handles special characters correctly.
     */
    public function test_slug_generation_handles_special_characters()
    {
        $testCases = [
            'Plugin & More',
            'Plugin @ Home',
            'Plugin #1',
            'Plugin $$$',
            'Plugin (Beta)',
            'Plugin [Test]',
            'Plugin {Dev}',
            'Plugin | Version',
            'Plugin / Slash',
            'Plugin \\ Backslash',
        ];
        
        foreach ($testCases as $name) {
            $user = User::factory()->create();
            $category = Category::factory()->create();
            
            $plugin = Plugin::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => $name,
                'slug' => '', // Trigger auto-generation
            ]);
            
            // Verify slug is URL-friendly
            $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $plugin->slug);
            
            // Verify slug is not empty
            $this->assertNotEmpty($plugin->slug);
            
            // Verify slug contains some alphanumeric characters
            $this->assertGreaterThan(0, preg_match('/[a-z0-9]/', $plugin->slug));
            
            // Clean up
            $plugin->delete();
            $user->delete();
            $category->delete();
        }
    }

    /**
     * @test
     * Feature: production-ready-marketplace, Property 8: Unique Slug Generation
     * 
     * Validates: Requirements 2.5
     * 
     * Edge case: Empty or whitespace-only names should still generate valid slugs.
     */
    public function test_slug_generation_handles_edge_case_names()
    {
        $edgeCases = [
            '   Spaces   ',
            'UPPERCASE',
            'lowercase',
            'MiXeD-CaSe',
            '123 Numbers',
            '---Dashes---',
            '___Underscores___',
        ];
        
        foreach ($edgeCases as $name) {
            $user = User::factory()->create();
            $category = Category::factory()->create();
            
            $plugin = Plugin::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => $name,
                'slug' => '', // Trigger auto-generation
            ]);
            
            // Verify slug was generated and is not empty
            $this->assertNotEmpty($plugin->slug);
            
            // Verify slug is URL-friendly
            $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $plugin->slug);
            
            // Clean up
            $plugin->delete();
            $user->delete();
            $category->delete();
        }
    }
}
