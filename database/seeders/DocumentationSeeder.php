<?php

namespace Database\Seeders;

use App\Models\Documentation;
use App\Models\DocumentationCategory;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
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
        ];

        foreach ($categories as $categoryData) {
            DocumentationCategory::create($categoryData);
        }

        // Create documentation
        $gettingStarted = DocumentationCategory::where('slug', 'getting-started')->first();
        $pluginDev = DocumentationCategory::where('slug', 'plugin-development')->first();
        $apiRef = DocumentationCategory::where('slug', 'api-reference')->first();
        $bestPractices = DocumentationCategory::where('slug', 'best-practices')->first();
        $troubleshooting = DocumentationCategory::where('slug', 'troubleshooting')->first();

        $docs = [
            // Getting Started
            [
                'category_id' => $gettingStarted->id,
                'title' => 'Introduction to Hyro',
                'excerpt' => 'Welcome to Hyro! Learn what Hyro is and how it can help you build better applications.',
                'content' => $this->getIntroductionContent(),
                'version' => '1.0',
                'order' => 1,
                'tags' => ['introduction', 'overview', 'basics']
            ],
            [
                'category_id' => $gettingStarted->id,
                'title' => 'Installation Guide',
                'excerpt' => 'Step-by-step instructions to install and configure Hyro in your project.',
                'content' => $this->getInstallationContent(),
                'version' => '1.0',
                'order' => 2,
                'tags' => ['installation', 'setup', 'configuration']
            ],
            [
                'category_id' => $gettingStarted->id,
                'title' => 'Quick Start Tutorial',
                'excerpt' => 'Get started with Hyro in 5 minutes with this quick tutorial.',
                'content' => $this->getQuickStartContent(),
                'version' => '1.0',
                'order' => 3,
                'tags' => ['tutorial', 'quickstart', 'beginner']
            ],

            // Plugin Development
            [
                'category_id' => $pluginDev->id,
                'title' => 'Creating Your First Plugin',
                'excerpt' => 'Learn how to create, test, and publish your first Hyro plugin.',
                'content' => $this->getFirstPluginContent(),
                'version' => '1.0',
                'order' => 1,
                'tags' => ['plugin', 'development', 'tutorial']
            ],
            [
                'category_id' => $pluginDev->id,
                'title' => 'Plugin Structure',
                'excerpt' => 'Understanding the anatomy of a Hyro plugin and its components.',
                'content' => $this->getPluginStructureContent(),
                'version' => '1.0',
                'order' => 2,
                'tags' => ['plugin', 'structure', 'architecture']
            ],
            [
                'category_id' => $pluginDev->id,
                'title' => 'Testing Plugins',
                'excerpt' => 'Best practices for testing your plugins before publishing.',
                'content' => $this->getTestingContent(),
                'version' => '1.0',
                'order' => 3,
                'tags' => ['testing', 'quality', 'debugging']
            ],

            // API Reference
            [
                'category_id' => $apiRef->id,
                'title' => 'Authentication API',
                'excerpt' => 'Complete reference for authentication endpoints and methods.',
                'content' => $this->getAuthAPIContent(),
                'version' => '1.0',
                'order' => 1,
                'tags' => ['api', 'authentication', 'security']
            ],
            [
                'category_id' => $apiRef->id,
                'title' => 'Plugin API',
                'excerpt' => 'API endpoints for managing plugins programmatically.',
                'content' => $this->getPluginAPIContent(),
                'version' => '1.0',
                'order' => 2,
                'tags' => ['api', 'plugins', 'endpoints']
            ],

            // Best Practices
            [
                'category_id' => $bestPractices->id,
                'title' => 'Security Best Practices',
                'excerpt' => 'Essential security guidelines for Hyro plugin development.',
                'content' => $this->getSecurityContent(),
                'version' => '1.0',
                'order' => 1,
                'tags' => ['security', 'best-practices', 'guidelines']
            ],
            [
                'category_id' => $bestPractices->id,
                'title' => 'Performance Optimization',
                'excerpt' => 'Tips and techniques to optimize your plugin performance.',
                'content' => $this->getPerformanceContent(),
                'version' => '1.0',
                'order' => 2,
                'tags' => ['performance', 'optimization', 'speed']
            ],

            // Troubleshooting
            [
                'category_id' => $troubleshooting->id,
                'title' => 'Common Installation Issues',
                'excerpt' => 'Solutions to frequently encountered installation problems.',
                'content' => $this->getInstallationIssuesContent(),
                'version' => '1.0',
                'order' => 1,
                'tags' => ['troubleshooting', 'installation', 'errors']
            ],
            [
                'category_id' => $troubleshooting->id,
                'title' => 'Plugin Compatibility Issues',
                'excerpt' => 'How to resolve plugin compatibility and conflict issues.',
                'content' => $this->getCompatibilityContent(),
                'version' => '1.0',
                'order' => 2,
                'tags' => ['troubleshooting', 'compatibility', 'conflicts']
            ],
        ];

        foreach ($docs as $docData) {
            Documentation::create($docData);
        }

        $this->command->info('Documentation seeded successfully.');
    }

    private function getIntroductionContent(): string
    {
        return <<<HTML
<h2>What is Hyro?</h2>
<p>Hyro is a powerful plugin marketplace platform built with Laravel and React. It allows developers to discover, share, and monetize plugins for various applications.</p>

<h3>Key Features</h3>
<ul>
<li><strong>Plugin Discovery:</strong> Browse thousands of plugins across multiple categories</li>
<li><strong>Easy Installation:</strong> One-click installation for most plugins</li>
<li><strong>Version Management:</strong> Support for multiple plugin versions</li>
<li><strong>Community Reviews:</strong> Read and write reviews to help others</li>
<li><strong>Developer Tools:</strong> Comprehensive API and development tools</li>
</ul>

<h3>Who is Hyro for?</h3>
<p>Hyro is designed for:</p>
<ul>
<li><strong>Developers:</strong> Build and publish plugins to reach a wide audience</li>
<li><strong>Users:</strong> Find and install plugins to enhance your applications</li>
<li><strong>Teams:</strong> Collaborate on plugin development and management</li>
</ul>

<h2>Getting Help</h2>
<p>If you need assistance, check out our <a href="/docs">documentation</a>, join our community forum, or contact support.</p>
HTML;
    }

    private function getInstallationContent(): string
    {
        return <<<HTML
<h2>System Requirements</h2>
<p>Before installing Hyro, ensure your system meets these requirements:</p>
<ul>
<li>PHP 8.1 or higher</li>
<li>Laravel 10.x or 11.x</li>
<li>Composer</li>
<li>Node.js 18+ and npm</li>
<li>MySQL 8.0+ or PostgreSQL 13+</li>
</ul>

<h2>Installation Steps</h2>

<h3>1. Install via Composer</h3>
<pre><code>composer require hyro/marketplace</code></pre>

<h3>2. Publish Configuration</h3>
<pre><code>php artisan vendor:publish --tag=hyro-config</code></pre>

<h3>3. Run Migrations</h3>
<pre><code>php artisan migrate</code></pre>

<h3>4. Install Frontend Assets</h3>
<pre><code>npm install
npm run build</code></pre>

<h3>5. Configure Environment</h3>
<p>Add these variables to your <code>.env</code> file:</p>
<pre><code>HYRO_API_KEY=your_api_key
HYRO_MARKETPLACE_URL=https://market.hyro.dev</code></pre>

<h2>Verification</h2>
<p>To verify the installation, run:</p>
<pre><code>php artisan hyro:status</code></pre>

<p>You should see a success message indicating Hyro is properly installed.</p>
HTML;
    }

    private function getQuickStartContent(): string
    {
        return <<<HTML
<h2>Your First Plugin Installation</h2>
<p>Let's install your first plugin in just a few steps!</p>

<h3>Step 1: Browse the Marketplace</h3>
<p>Visit the <a href="/market">marketplace</a> and browse available plugins. Use filters to find plugins by category, rating, or popularity.</p>

<h3>Step 2: Choose a Plugin</h3>
<p>Click on any plugin to view its details, including:</p>
<ul>
<li>Description and features</li>
<li>Version compatibility</li>
<li>User reviews and ratings</li>
<li>Installation instructions</li>
</ul>

<h3>Step 3: Install the Plugin</h3>
<p>Click the "Download / Install" button. The plugin will be automatically downloaded and installed.</p>

<h3>Step 4: Configure the Plugin</h3>
<p>After installation, configure the plugin according to its documentation:</p>
<pre><code>php artisan vendor:publish --tag=plugin-name-config
php artisan plugin:configure plugin-name</code></pre>

<h3>Step 5: Start Using</h3>
<p>That's it! Your plugin is now ready to use. Check the plugin's documentation for usage examples and API reference.</p>

<h2>Next Steps</h2>
<ul>
<li>Explore more plugins in the marketplace</li>
<li>Learn about <a href="/docs/plugin-development">plugin development</a></li>
<li>Join the community forum</li>
</ul>
HTML;
    }

    private function getFirstPluginContent(): string
    {
        return <<<HTML
<h2>Creating Your First Plugin</h2>
<p>This guide will walk you through creating a simple Hyro plugin from scratch.</p>

<h3>Step 1: Generate Plugin Scaffold</h3>
<pre><code>php artisan hyro:make-plugin MyAwesomePlugin</code></pre>

<h3>Step 2: Plugin Structure</h3>
<p>Your plugin will have this structure:</p>
<pre><code>plugins/
  my-awesome-plugin/
    src/
      MyAwesomePlugin.php
    config/
      config.php
    resources/
      views/
      assets/
    composer.json
    README.md</code></pre>

<h3>Step 3: Implement Core Functionality</h3>
<p>Edit <code>src/MyAwesomePlugin.php</code>:</p>
<pre><code>namespace Hyro\\Plugins\\MyAwesomePlugin;

class MyAwesomePlugin
{
    public function boot()
    {
        // Plugin initialization code
    }

    public function register()
    {
        // Register services
    }
}</code></pre>

<h3>Step 4: Add Configuration</h3>
<p>Define plugin settings in <code>config/config.php</code>:</p>
<pre><code>return [
    'enabled' => true,
    'api_key' => env('MY_PLUGIN_API_KEY'),
    'options' => [
        'feature_a' => true,
        'feature_b' => false,
    ],
];</code></pre>

<h3>Step 5: Test Your Plugin</h3>
<pre><code>php artisan test plugins/my-awesome-plugin</code></pre>

<h3>Step 6: Publish to Marketplace</h3>
<p>Once tested, publish your plugin:</p>
<pre><code>php artisan hyro:publish my-awesome-plugin</code></pre>

<h2>Best Practices</h2>
<ul>
<li>Follow PSR-12 coding standards</li>
<li>Write comprehensive tests</li>
<li>Document all public APIs</li>
<li>Use semantic versioning</li>
<li>Include a detailed README</li>
</ul>
HTML;
    }

    private function getPluginStructureContent(): string
    {
        return <<<HTML
<h2>Plugin Anatomy</h2>
<p>Understanding the structure of a Hyro plugin is essential for effective development.</p>

<h3>Directory Structure</h3>
<pre><code>my-plugin/
├── src/                    # Source code
│   ├── Controllers/        # HTTP controllers
│   ├── Models/            # Database models
│   ├── Services/          # Business logic
│   └── Plugin.php         # Main plugin class
├── config/                # Configuration files
├── database/              # Migrations and seeders
├── resources/             # Views and assets
│   ├── views/
│   ├── js/
│   └── css/
├── routes/                # Route definitions
├── tests/                 # Test files
├── composer.json          # Dependencies
├── package.json           # Frontend dependencies
└── README.md             # Documentation</code></pre>

<h3>Core Components</h3>

<h4>Plugin Class</h4>
<p>The main entry point for your plugin:</p>
<pre><code>class MyPlugin extends BasePlugin
{
    public function boot() { }
    public function register() { }
    public function activate() { }
    public function deactivate() { }
}</code></pre>

<h4>Service Provider</h4>
<p>Register services and bindings:</p>
<pre><code>class MyPluginServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register singleton service
        app()->singleton(MyService::class);
    }
}</code></pre>

<h4>Configuration</h4>
<p>Plugin settings and options:</p>
<pre><code>return [
    'name' => 'My Plugin',
    'version' => '1.0.0',
    'author' => 'Your Name',
    'dependencies' => [],
];</code></pre>

<h3>Lifecycle Hooks</h3>
<ul>
<li><code>install()</code> - Called when plugin is first installed</li>
<li><code>activate()</code> - Called when plugin is activated</li>
<li><code>deactivate()</code> - Called when plugin is deactivated</li>
<li><code>uninstall()</code> - Called when plugin is removed</li>
</ul>
HTML;
    }

    private function getTestingContent(): string
    {
        return <<<HTML
<h2>Testing Your Plugin</h2>
<p>Comprehensive testing ensures your plugin works reliably for all users.</p>

<h3>Unit Tests</h3>
<p>Test individual components in isolation:</p>
<pre><code>class MyPluginTest extends TestCase
{
    public function test_plugin_initializes()
    {
        $plugin = new MyPlugin();
        $this->assertInstanceOf(MyPlugin::class, $plugin);
    }

    public function test_configuration_loads()
    {
        $config = config('my-plugin');
        $this->assertIsArray($config);
    }
}</code></pre>

<h3>Feature Tests</h3>
<p>Test complete features and workflows:</p>
<pre><code>public function test_user_can_access_plugin_page()
{
    $response = $this->get('/my-plugin');
    $response->assertStatus(200);
    $response->assertSee('My Plugin');
}</code></pre>

<h3>Integration Tests</h3>
<p>Test plugin integration with the main application:</p>
<pre><code>public function test_plugin_registers_routes()
{
    $routes = Route::getRoutes();
    $this->assertTrue($routes->hasNamedRoute('my-plugin.index'));
}</code></pre>

<h3>Running Tests</h3>
<pre><code># Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/MyPluginTest.php

# Run with coverage
php artisan test --coverage</code></pre>

<h3>Testing Checklist</h3>
<ul>
<li>✓ All public methods have tests</li>
<li>✓ Edge cases are covered</li>
<li>✓ Error handling is tested</li>
<li>✓ Database interactions work correctly</li>
<li>✓ API endpoints return expected responses</li>
<li>✓ Frontend components render properly</li>
</ul>
HTML;
    }

    private function getAuthAPIContent(): string
    {
        return <<<HTML
<h2>Authentication API</h2>
<p>Secure API authentication using tokens and OAuth.</p>

<h3>Obtaining an API Token</h3>
<p>POST request to generate a new API token:</p>
<pre><code>POST /api/auth/token
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "expires_at": "2026-03-15T12:00:00Z"
}</code></pre>

<h3>Using the Token</h3>
<p>Include the token in the Authorization header:</p>
<pre><code>GET /api/plugins
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...</code></pre>

<h3>Token Refresh</h3>
<pre><code>POST /api/auth/refresh
Authorization: Bearer [old_token]

Response:
{
  "token": "new_token_here",
  "expires_at": "2026-03-16T12:00:00Z"
}</code></pre>

<h3>Logout</h3>
<pre><code>POST /api/auth/logout
Authorization: Bearer [token]

Response:
{
  "message": "Successfully logged out"
}</code></pre>

<h3>OAuth 2.0</h3>
<p>Hyro supports OAuth 2.0 for third-party integrations:</p>
<pre><code>GET /oauth/authorize?
  client_id=YOUR_CLIENT_ID&
  redirect_uri=YOUR_REDIRECT_URI&
  response_type=code&
  scope=read write</code></pre>
HTML;
    }

    private function getPluginAPIContent(): string
    {
        return <<<HTML
<h2>Plugin API Endpoints</h2>
<p>Programmatically manage plugins through our REST API.</p>

<h3>List All Plugins</h3>
<pre><code>GET /api/plugins?page=1&per_page=20

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Plugin Name",
      "slug": "plugin-name",
      "version": "1.0.0",
      "downloads": 1234,
      "rating_avg": 4.5
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 100
  }
}</code></pre>

<h3>Get Plugin Details</h3>
<pre><code>GET /api/plugins/{slug}

Response:
{
  "id": 1,
  "name": "Plugin Name",
  "description": "Plugin description",
  "version": "1.0.0",
  "author": {
    "id": 1,
    "name": "Author Name"
  },
  "requirements": {
    "php": ">=8.1",
    "laravel": ">=10.0"
  }
}</code></pre>

<h3>Install Plugin</h3>
<pre><code>POST /api/plugins/{slug}/install
Authorization: Bearer [token]

Response:
{
  "message": "Plugin installed successfully",
  "plugin": { ... }
}</code></pre>

<h3>Update Plugin</h3>
<pre><code>PUT /api/plugins/{slug}/update
Authorization: Bearer [token]

Response:
{
  "message": "Plugin updated to version 1.1.0"
}</code></pre>

<h3>Uninstall Plugin</h3>
<pre><code>DELETE /api/plugins/{slug}
Authorization: Bearer [token]

Response:
{
  "message": "Plugin uninstalled successfully"
}</code></pre>
HTML;
    }

    private function getSecurityContent(): string
    {
        return <<<HTML
<h2>Security Best Practices</h2>
<p>Follow these guidelines to ensure your plugin is secure.</p>

<h3>Input Validation</h3>
<p>Always validate and sanitize user input:</p>
<pre><code>$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email',
    'age' => 'required|integer|min:18',
]);</code></pre>

<h3>SQL Injection Prevention</h3>
<p>Use Eloquent ORM or prepared statements:</p>
<pre><code>// Good
$users = User::where('email', $email)->get();

// Bad - Never do this!
$users = DB::select("SELECT * FROM users WHERE email = '$email'");</code></pre>

<h3>XSS Protection</h3>
<p>Escape output in views:</p>
<pre><code>// Blade automatically escapes
{{ $user->name }}

// Raw output (use with caution)
{!! $trustedHtml !!}</code></pre>

<h3>CSRF Protection</h3>
<p>Include CSRF token in forms:</p>
<pre><code>&lt;form method="POST"&gt;
    @csrf
    &lt;!-- form fields --&gt;
&lt;/form&gt;</code></pre>

<h3>Authentication & Authorization</h3>
<pre><code>// Check authentication
if (auth()->check()) {
    // User is logged in
}

// Check authorization
if (auth()->user()->can('edit-plugin', $plugin)) {
    // User has permission
}</code></pre>

<h3>Secure Configuration</h3>
<ul>
<li>Never commit sensitive data to version control</li>
<li>Use environment variables for secrets</li>
<li>Encrypt sensitive database fields</li>
<li>Use HTTPS in production</li>
<li>Keep dependencies updated</li>
</ul>
HTML;
    }

    private function getPerformanceContent(): string
    {
        return <<<HTML
<h2>Performance Optimization</h2>
<p>Tips to make your plugin fast and efficient.</p>

<h3>Database Optimization</h3>

<h4>Use Eager Loading</h4>
<pre><code>// Bad - N+1 query problem
$plugins = Plugin::all();
foreach ($plugins as $plugin) {
    echo $plugin->user->name;
}

// Good - Eager loading
$plugins = Plugin::with('user')->get();
foreach ($plugins as $plugin) {
    echo $plugin->user->name;
}</code></pre>

<h4>Add Indexes</h4>
<pre><code>Schema::table('plugins', function (Blueprint $table) {
    $table->index('slug');
    $table->index('category_id');
    $table->index(['status', 'published_at']);
});</code></pre>

<h3>Caching</h3>
<pre><code>// Cache expensive operations
$plugins = Cache::remember('popular-plugins', 3600, function () {
    return Plugin::where('downloads', '>', 1000)
        ->orderBy('downloads', 'desc')
        ->limit(10)
        ->get();
});</code></pre>

<h3>Queue Long-Running Tasks</h3>
<pre><code>// Dispatch to queue
ProcessPluginInstallation::dispatch($plugin);

// Job class
class ProcessPluginInstallation implements ShouldQueue
{
    public function handle()
    {
        // Long-running task
    }
}</code></pre>

<h3>Asset Optimization</h3>
<ul>
<li>Minify CSS and JavaScript</li>
<li>Use CDN for static assets</li>
<li>Implement lazy loading for images</li>
<li>Enable gzip compression</li>
<li>Use HTTP/2</li>
</ul>

<h3>Code Optimization</h3>
<ul>
<li>Avoid unnecessary loops</li>
<li>Use collections efficiently</li>
<li>Implement pagination for large datasets</li>
<li>Profile and identify bottlenecks</li>
</ul>
HTML;
    }

    private function getInstallationIssuesContent(): string
    {
        return <<<HTML
<h2>Common Installation Issues</h2>
<p>Solutions to frequently encountered problems during installation.</p>

<h3>Composer Dependency Conflicts</h3>
<p><strong>Problem:</strong> Composer reports dependency conflicts.</p>
<p><strong>Solution:</strong></p>
<pre><code># Update composer
composer self-update

# Clear cache
composer clear-cache

# Try installing with --ignore-platform-reqs (use cautiously)
composer install --ignore-platform-reqs</code></pre>

<h3>Permission Errors</h3>
<p><strong>Problem:</strong> Permission denied errors during installation.</p>
<p><strong>Solution:</strong></p>
<pre><code># Set correct permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Or for development
chmod -R 777 storage bootstrap/cache</code></pre>

<h3>Database Connection Failed</h3>
<p><strong>Problem:</strong> Cannot connect to database.</p>
<p><strong>Solution:</strong></p>
<ul>
<li>Verify database credentials in <code>.env</code></li>
<li>Ensure database server is running</li>
<li>Check firewall settings</li>
<li>Test connection: <code>php artisan db:show</code></li>
</ul>

<h3>Node/NPM Errors</h3>
<p><strong>Problem:</strong> Frontend build fails.</p>
<p><strong>Solution:</strong></p>
<pre><code># Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Use specific Node version
nvm use 18
npm install</code></pre>

<h3>Migration Errors</h3>
<p><strong>Problem:</strong> Migrations fail to run.</p>
<p><strong>Solution:</strong></p>
<pre><code># Check migration status
php artisan migrate:status

# Rollback and retry
php artisan migrate:rollback
php artisan migrate

# Fresh install (WARNING: deletes all data)
php artisan migrate:fresh</code></pre>
HTML;
    }

    private function getCompatibilityContent(): string
    {
        return <<<HTML
<h2>Plugin Compatibility Issues</h2>
<p>Resolving conflicts between plugins and versions.</p>

<h3>Version Conflicts</h3>
<p><strong>Problem:</strong> Plugin requires different Laravel version.</p>
<p><strong>Solution:</strong></p>
<ul>
<li>Check plugin compatibility in marketplace</li>
<li>Look for updated version of the plugin</li>
<li>Contact plugin author for compatibility update</li>
<li>Consider alternative plugins</li>
</ul>

<h3>Route Conflicts</h3>
<p><strong>Problem:</strong> Multiple plugins register same routes.</p>
<p><strong>Solution:</strong></p>
<pre><code>// In plugin configuration
'route_prefix' => 'my-plugin',

// Routes will be: /my-plugin/dashboard instead of /dashboard</code></pre>

<h3>Service Provider Conflicts</h3>
<p><strong>Problem:</strong> Plugins override same services.</p>
<p><strong>Solution:</strong></p>
<ul>
<li>Check plugin load order in <code>config/app.php</code></li>
<li>Use plugin priority settings</li>
<li>Disable conflicting plugin temporarily</li>
</ul>

<h3>Asset Conflicts</h3>
<p><strong>Problem:</strong> CSS/JS conflicts between plugins.</p>
<p><strong>Solution:</strong></p>
<ul>
<li>Use namespaced CSS classes</li>
<li>Load plugin assets conditionally</li>
<li>Use CSS modules or scoped styles</li>
<li>Check browser console for errors</li>
</ul>

<h3>Database Conflicts</h3>
<p><strong>Problem:</strong> Plugins create same table names.</p>
<p><strong>Solution:</strong></p>
<pre><code>// Use prefixed table names
Schema::create('myplugin_users', function (Blueprint $table) {
    // ...
});</code></pre>

<h3>Debugging Tips</h3>
<pre><code># Enable debug mode
APP_DEBUG=true

# Check logs
tail -f storage/logs/laravel.log

# List installed plugins
php artisan plugin:list

# Disable all plugins
php artisan plugin:disable --all

# Enable plugins one by one to find conflict
php artisan plugin:enable plugin-name</code></pre>
HTML;
    }
}
