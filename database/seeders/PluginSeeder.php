<?php

namespace Database\Seeders;

use App\Models\Plugin;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PluginSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();

        if ($users->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Please seed users and categories first.');
            return;
        }

        $plugins = [
            [
                'name' => 'Advanced Authentication',
                'description' => 'Multi-factor authentication with social login integration for Laravel applications. Supports Google, Facebook, Twitter, and GitHub OAuth providers. Includes two-factor authentication via SMS and email.',
                'version' => '1.0.0',
                'compatibility' => 'Laravel 10.x, 11.x',
                'license_type' => 'MIT',
                'status' => 'active',
            ],
            [
                'name' => 'Payment Gateway Pro',
                'description' => 'Comprehensive payment processing with support for Stripe, PayPal, and more. Handle subscriptions, one-time payments, and refunds with ease. Includes webhook handling and detailed transaction logging.',
                'version' => '2.1.0',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'Proprietary',
                'status' => 'active',
            ],
            [
                'name' => 'SEO Optimizer',
                'description' => 'Automatic meta tags, sitemap generation, and SEO best practices for your Laravel app. Includes structured data markup, Open Graph tags, and Twitter Cards support.',
                'version' => '1.5.2',
                'compatibility' => 'Laravel 9.x, 10.x, 11.x',
                'license_type' => 'GPL',
                'status' => 'active',
            ],
            [
                'name' => 'Analytics Dashboard',
                'description' => 'Beautiful analytics dashboard with real-time data visualization. Track user behavior, page views, conversions, and custom events. Integrates with Google Analytics and custom tracking solutions.',
                'version' => '3.0.1',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'MIT',
                'status' => 'active',
            ],
            [
                'name' => 'Social Media Manager',
                'description' => 'Schedule and publish posts to multiple social media platforms from your Laravel application. Supports Facebook, Twitter, Instagram, and LinkedIn.',
                'version' => '1.2.0',
                'compatibility' => 'Laravel 10.x, 11.x',
                'license_type' => 'Apache',
                'status' => 'active',
            ],
            [
                'name' => 'E-commerce Toolkit',
                'description' => 'Complete e-commerce solution with shopping cart, checkout, inventory management, and order tracking. Includes support for multiple currencies and tax calculations.',
                'version' => '2.5.0',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'Proprietary',
                'status' => 'pending',
            ],
            [
                'name' => 'Security Scanner',
                'description' => 'Automated security scanning for Laravel applications. Detects common vulnerabilities, outdated dependencies, and security misconfigurations.',
                'version' => '1.0.0',
                'compatibility' => 'Laravel 10.x, 11.x',
                'license_type' => 'MIT',
                'status' => 'pending',
            ],
            [
                'name' => 'Performance Monitor',
                'description' => 'Real-time performance monitoring with detailed metrics on database queries, API calls, and page load times. Includes alerting and historical data analysis.',
                'version' => '1.8.3',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'GPL',
                'status' => 'active',
            ],
            [
                'name' => 'UI Component Library',
                'description' => 'Comprehensive collection of reusable UI components for Laravel and Inertia.js. Includes forms, tables, modals, notifications, and more.',
                'version' => '4.2.1',
                'compatibility' => 'Laravel 10.x, 11.x',
                'license_type' => 'MIT',
                'status' => 'active',
            ],
            [
                'name' => 'API Integration Hub',
                'description' => 'Simplify third-party API integrations with pre-built connectors for popular services. Includes rate limiting, caching, and error handling.',
                'version' => '1.3.0',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'Apache',
                'status' => 'inactive',
            ],
        ];

        foreach ($plugins as $pluginData) {
            $isActive = $pluginData['status'] === 'active';
            
            Plugin::create([
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
                'name' => $pluginData['name'],
                'slug' => Str::slug($pluginData['name']),
                'description' => $pluginData['description'],
                'version' => $pluginData['version'],
                'status' => $pluginData['status'],
                'compatibility' => $pluginData['compatibility'],
                'requirements' => [
                    'php' => '>=8.1',
                    'laravel' => '>=10.0',
                ],
                'license_type' => $pluginData['license_type'],
                'downloads' => $isActive ? rand(100, 5000) : 0,
                'rating_avg' => 0.00, // Will be calculated by ReviewSeeder
                'rating_count' => 0,
                'published_at' => $isActive ? now()->subDays(rand(1, 90)) : null,
            ]);
        }

        $this->command->info('Plugins seeded successfully.');
    }
}
