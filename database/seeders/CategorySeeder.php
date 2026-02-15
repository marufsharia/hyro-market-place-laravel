<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // 1. Security & Authentication
            [
                'name' => 'Security & Authentication',
                'slug' => 'security-authentication',
            ],
            // 2. E-commerce & Payments
            [
                'name' => 'E-commerce & Payments',
                'slug' => 'ecommerce-payments',
            ],
            // 3. Marketing & SEO
            [
                'name' => 'Marketing & SEO',
                'slug' => 'marketing-seo',
            ],
            // 4. UI & Design
            [
                'name' => 'UI & Design',
                'slug' => 'ui-design',
            ],
            // 5. Development Tools
            [
                'name' => 'Development Tools',
                'slug' => 'development-tools',
            ],
            // 6. Performance & Monitoring
            [
                'name' => 'Performance & Monitoring',
                'slug' => 'performance-monitoring',
            ],
            // 7. API & Integration
            [
                'name' => 'API & Integration',
                'slug' => 'api-integration',
            ],
            // 8. Content Management
            [
                'name' => 'Content Management',
                'slug' => 'content-management',
            ],
            // 9. Communication
            [
                'name' => 'Communication',
                'slug' => 'communication',
            ],
            // 10. Data & Analytics
            [
                'name' => 'Data & Analytics',
                'slug' => 'data-analytics',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        $this->command->info('Categories seeded successfully (10 total).');
    }
}
