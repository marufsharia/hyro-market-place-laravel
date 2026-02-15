<?php

namespace Database\Seeders;

use App\Models\Documentation;
use App\Models\DocumentationCategory;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 different documentation categories
        $categories = [
            [
                'name' => 'Getting Started',
                'slug' => 'getting-started',
                'description' => 'Learn the basics of Hyro and get up and running quickly',
                'icon' => '🚀',
                'order' => 1
            ],
            [
                'name' => 'Plugin Development',
                'slug' => 'plugin-development',
                'description' => 'Build and publish your own Hyro plugins',
                'icon' => '🔧',
                'order' => 2
            ],
            [
                'name' => 'API Reference',
                'slug' => 'api-reference',
                'description' => 'Complete API documentation and endpoints',
                'icon' => '📡',
                'order' => 3
            ],
            [
                'name' => 'Best Practices',
                'slug' => 'best-practices',
                'description' => 'Tips and guidelines for optimal development',
                'icon' => '⭐',
                'order' => 4
            ],
            [
                'name' => 'Troubleshooting',
                'slug' => 'troubleshooting',
                'description' => 'Common issues and how to solve them',
                'icon' => '🔍',
                'order' => 5
            ],
            [
                'name' => 'Security',
                'slug' => 'security',
                'description' => 'Security guidelines and best practices',
                'icon' => '🔒',
                'order' => 6
            ],
            [
                'name' => 'Deployment',
                'slug' => 'deployment',
                'description' => 'Deploy your plugins to production',
                'icon' => '🚢',
                'order' => 7
            ],
            [
                'name' => 'Testing',
                'slug' => 'testing',
                'description' => 'Testing strategies and frameworks',
                'icon' => '🧪',
                'order' => 8
            ],
            [
                'name' => 'Performance',
                'slug' => 'performance',
                'description' => 'Optimize your plugins for speed',
                'icon' => '⚡',
                'order' => 9
            ],
            [
                'name' => 'Community',
                'slug' => 'community',
                'description' => 'Community resources and contributions',
                'icon' => '👥',
                'order' => 10
            ],
        ];

        foreach ($categories as $categoryData) {
            DocumentationCategory::create($categoryData);
        }

        // Now create 50+ documentation articles using factory
        $gettingStarted = DocumentationCategory::where('slug', 'getting-started')->first();
        $pluginDev = DocumentationCategory::where('slug', 'plugin-development')->first();
        $apiRef = DocumentationCategory::where('slug', 'api-reference')->first();
        $bestPractices = DocumentationCategory::where('slug', 'best-practices')->first();
        $troubleshooting = DocumentationCategory::where('slug', 'troubleshooting')->first();
        $security = DocumentationCategory::where('slug', 'security')->first();
        $deployment = DocumentationCategory::where('slug', 'deployment')->first();
        $testing = DocumentationCategory::where('slug', 'testing')->first();
        $performance = DocumentationCategory::where('slug', 'performance')->first();
        $community = DocumentationCategory::where('slug', 'community')->first();

        // Getting Started - 5 docs
        Documentation::factory(5)->create([
            'category_id' => $gettingStarted->id,
            'version' => '1.0',
        ]);

        // Plugin Development - 8 docs
        Documentation::factory(8)->create([
            'category_id' => $pluginDev->id,
            'version' => '1.0',
        ]);

        // API Reference - 10 docs
        Documentation::factory(10)->create([
            'category_id' => $apiRef->id,
            'version' => '1.0',
        ]);

        // Best Practices - 6 docs
        Documentation::factory(6)->create([
            'category_id' => $bestPractices->id,
            'version' => '1.0',
        ]);

        // Troubleshooting - 7 docs
        Documentation::factory(7)->create([
            'category_id' => $troubleshooting->id,
            'version' => '1.0',
        ]);

        // Security - 5 docs
        Documentation::factory(5)->create([
            'category_id' => $security->id,
            'version' => '1.0',
        ]);

        // Deployment - 4 docs
        Documentation::factory(4)->create([
            'category_id' => $deployment->id,
            'version' => '1.0',
        ]);

        // Testing - 5 docs
        Documentation::factory(5)->create([
            'category_id' => $testing->id,
            'version' => '1.0',
        ]);

        // Performance - 5 docs
        Documentation::factory(5)->create([
            'category_id' => $performance->id,
            'version' => '1.0',
        ]);

        // Community - 3 docs
        Documentation::factory(3)->create([
            'category_id' => $community->id,
            'version' => '1.0',
        ]);

        $this->command->info('Documentation seeded successfully (10 categories, 58 articles).');
    }
}
