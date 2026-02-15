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
            // 1. Authentication Plugin
            [
                'name' => 'Advanced Authentication Suite',
                'description' => 'Multi-factor authentication with social login integration for Laravel applications. Supports Google, Facebook, Twitter, and GitHub OAuth providers. Includes two-factor authentication via SMS and email, biometric authentication, and passwordless login options.',
                'version' => '2.5.0',
                'compatibility' => 'Laravel 10.x, 11.x',
                'license_type' => 'MIT',
                'status' => 'active',
                'type' => 'security',
            ],
            // 2. Payment Gateway
            [
                'name' => 'Payment Gateway Pro',
                'description' => 'Comprehensive payment processing with support for Stripe, PayPal, Square, and more. Handle subscriptions, one-time payments, refunds, and invoicing with ease. Includes webhook handling, detailed transaction logging, and fraud detection.',
                'version' => '3.2.1',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'Proprietary',
                'status' => 'active',
                'type' => 'commerce',
            ],
            // 3. SEO Tool
            [
                'name' => 'SEO Optimizer Pro',
                'description' => 'Automatic meta tags, sitemap generation, and SEO best practices for your Laravel app. Includes structured data markup, Open Graph tags, Twitter Cards support, canonical URLs, and automatic image optimization for better search rankings.',
                'version' => '1.8.5',
                'compatibility' => 'Laravel 9.x, 10.x, 11.x',
                'license_type' => 'GPL',
                'status' => 'active',
                'type' => 'marketing',
            ],
            // 4. Analytics Dashboard
            [
                'name' => 'Analytics Dashboard Ultimate',
                'description' => 'Beautiful analytics dashboard with real-time data visualization and insights. Track user behavior, page views, conversions, custom events, and revenue. Integrates with Google Analytics, Mixpanel, and custom tracking solutions. Includes AI-powered insights.',
                'version' => '4.1.0',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'MIT',
                'status' => 'active',
                'type' => 'analytics',
            ],
            // 5. Social Media Manager
            [
                'name' => 'Social Media Automation Hub',
                'description' => 'Schedule and publish posts to multiple social media platforms from your Laravel application. Supports Facebook, Twitter, Instagram, LinkedIn, TikTok, and Pinterest. Includes analytics, hashtag suggestions, and content calendar.',
                'version' => '2.0.3',
                'compatibility' => 'Laravel 10.x, 11.x',
                'license_type' => 'Apache',
                'status' => 'active',
                'type' => 'marketing',
            ],
            // 6. E-commerce Toolkit
            [
                'name' => 'E-commerce Complete Solution',
                'description' => 'Complete e-commerce solution with shopping cart, checkout, inventory management, order tracking, and customer management. Includes support for multiple currencies, tax calculations, shipping integrations, and abandoned cart recovery.',
                'version' => '3.5.2',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'Proprietary',
                'status' => 'active',
                'type' => 'commerce',
            ],
            // 7. Security Scanner
            [
                'name' => 'Security Scanner & Firewall',
                'description' => 'Automated security scanning for Laravel applications. Detects common vulnerabilities, outdated dependencies, security misconfigurations, SQL injection attempts, XSS attacks, and CSRF vulnerabilities. Includes real-time threat monitoring.',
                'version' => '1.5.0',
                'compatibility' => 'Laravel 10.x, 11.x',
                'license_type' => 'MIT',
                'status' => 'active',
                'type' => 'security',
            ],
            // 8. Performance Monitor
            [
                'name' => 'Performance Monitor Pro',
                'description' => 'Real-time performance monitoring with detailed metrics on database queries, API calls, page load times, and memory usage. Includes alerting, historical data analysis, bottleneck detection, and optimization recommendations.',
                'version' => '2.3.7',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'GPL',
                'status' => 'active',
                'type' => 'development',
            ],
            // 9. UI Component Library
            [
                'name' => 'UI Component Library Premium',
                'description' => 'Comprehensive collection of 200+ reusable UI components for Laravel and Inertia.js. Includes forms, tables, modals, notifications, charts, calendars, file uploaders, and more. Fully customizable with Tailwind CSS and dark mode support.',
                'version' => '5.0.1',
                'compatibility' => 'Laravel 10.x, 11.x',
                'license_type' => 'MIT',
                'status' => 'active',
                'type' => 'ui',
            ],
            // 10. API Integration Hub
            [
                'name' => 'API Integration Hub',
                'description' => 'Simplify third-party API integrations with pre-built connectors for 50+ popular services including Slack, Twilio, SendGrid, AWS, and more. Includes rate limiting, caching, error handling, retry logic, and webhook management.',
                'version' => '1.9.0',
                'compatibility' => 'Laravel 11.x',
                'license_type' => 'Apache',
                'status' => 'active',
                'type' => 'integration',
            ],
        ];

        foreach ($plugins as $index => $pluginData) {
            $isActive = $pluginData['status'] === 'active';
            $user = $users->skip($index % $users->count())->first();
            $category = $categories->random();
            
            Plugin::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => $pluginData['name'],
                'slug' => Str::slug($pluginData['name']),
                'description' => $pluginData['description'],
                'version' => $pluginData['version'],
                'status' => $pluginData['status'],
                'compatibility' => $pluginData['compatibility'],
                'requirements' => [
                    'php' => '>=8.1',
                    'laravel' => '>=10.0',
                    'composer' => '>=2.0',
                    'node' => '>=18.0',
                ],
                'changelog' => [
                    [
                        'version' => $pluginData['version'],
                        'date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                        'changes' => [
                            'New features and improvements',
                            'Bug fixes and performance enhancements',
                            'Updated documentation',
                            'Security patches applied'
                        ]
                    ],
                    [
                        'version' => $this->getPreviousVersion($pluginData['version']),
                        'date' => now()->subDays(rand(31, 90))->format('Y-m-d'),
                        'changes' => [
                            'Initial stable release',
                            'Core functionality implemented',
                            'Comprehensive testing completed'
                        ]
                    ]
                ],
                'installation_instructions' => "# Installation\n\ncomposer require hyro/" . Str::slug($pluginData['name']) . "\n\n# Publish Configuration\n\nphp artisan vendor:publish --tag=hyro-" . Str::slug($pluginData['name']) . "\n\n# Run Migrations\n\nphp artisan migrate\n\n# Install Frontend Assets\n\nnpm install && npm run build",
                'documentation_url' => 'https://docs.hyro.dev/' . Str::slug($pluginData['name']),
                'support_url' => 'https://support.hyro.dev/' . Str::slug($pluginData['name']),
                'demo_url' => 'https://demo.hyro.dev/' . Str::slug($pluginData['name']),
                'repository_url' => 'https://github.com/hyro/' . Str::slug($pluginData['name']),
                'license_type' => $pluginData['license_type'],
                'downloads' => $isActive ? rand(500, 10000) : 0,
                'rating_avg' => 0.00,
                'rating_count' => 0,
                'published_at' => $isActive ? now()->subDays(rand(1, 180)) : null,
            ]);
        }

        // Create 15 additional random plugins using factory
        Plugin::factory(15)->create();

        $this->command->info('Plugins seeded successfully (25 total).');
    }

    private function getPreviousVersion(string $version): string
    {
        $parts = explode('.', $version);
        if (isset($parts[1]) && $parts[1] > 0) {
            $parts[1]--;
        } elseif (isset($parts[0]) && $parts[0] > 0) {
            $parts[0]--;
            $parts[1] = 9;
        }
        return implode('.', $parts);
    }
}
