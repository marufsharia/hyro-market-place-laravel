<?php

namespace Database\Factories;

use App\Models\DocumentationCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentationFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(rand(3, 6));
        
        return [
            'category_id' => DocumentationCategory::factory(),
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 10000),
            'excerpt' => fake()->sentence(15),
            'content' => $this->generateContent(),
            'version' => fake()->randomElement(['1.0', '1.1', '1.2', '2.0', '2.1']),
            'order' => fake()->numberBetween(1, 100),
            'is_published' => fake()->boolean(90), // 90% published
            'tags' => fake()->randomElements(['tutorial', 'guide', 'api', 'security', 'performance', 'testing', 'deployment', 'configuration', 'troubleshooting', 'best-practices'], rand(2, 4)),
            'views' => fake()->numberBetween(0, 5000),
        ];
    }

    private function generateContent(): string
    {
        $sections = rand(3, 6);
        $content = '';

        for ($i = 0; $i < $sections; $i++) {
            $content .= '<h2>' . fake()->sentence(rand(2, 5)) . '</h2>';
            $content .= '<p>' . fake()->paragraph(rand(3, 6)) . '</p>';

            // Add code block sometimes
            if (fake()->boolean(40)) {
                $content .= '<pre><code>' . $this->generateCode() . '</code></pre>';
            }

            // Add list sometimes
            if (fake()->boolean(50)) {
                $content .= '<ul>';
                for ($j = 0; $j < rand(3, 5); $j++) {
                    $content .= '<li>' . fake()->sentence() . '</li>';
                }
                $content .= '</ul>';
            }

            // Add subsection sometimes
            if (fake()->boolean(60)) {
                $content .= '<h3>' . fake()->sentence(rand(2, 4)) . '</h3>';
                $content .= '<p>' . fake()->paragraph(rand(2, 4)) . '</p>';
            }
        }

        return $content;
    }

    private function generateCode(): string
    {
        $codeExamples = [
            "composer require hyro/plugin\nphp artisan vendor:publish --tag=hyro-config",
            "php artisan make:plugin MyPlugin\nphp artisan plugin:install my-plugin",
            "Route::get('/api/plugins', [PluginController::class, 'index']);\nRoute::post('/api/plugins', [PluginController::class, 'store']);",
            "class MyPlugin extends BasePlugin\n{\n    public function boot()\n    {\n        // Plugin initialization\n    }\n}",
            "public function test_plugin_loads()\n{\n    \$plugin = new MyPlugin();\n    \$this->assertInstanceOf(MyPlugin::class, \$plugin);\n}",
            "Cache::remember('plugins', 3600, function () {\n    return Plugin::all();\n});",
            "\$validated = \$request->validate([\n    'name' => 'required|string|max:255',\n    'version' => 'required|string',\n]);",
            "DB::table('plugins')\n    ->where('status', 'active')\n    ->orderBy('downloads', 'desc')\n    ->limit(10)\n    ->get();",
        ];

        return fake()->randomElement($codeExamples);
    }
}
