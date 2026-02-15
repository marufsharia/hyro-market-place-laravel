# Design Document: Production-Ready Marketplace

## Overview

This design transforms the Hyro Marketplace from a basic prototype into a production-ready Laravel application. The architecture follows Laravel best practices with a clear separation between public marketplace functionality, authenticated user features, and administrative operations. The system uses Laravel 11 with React/Inertia for the frontend, Livewire for admin panels, and Redis for caching and session management.

The design addresses critical gaps including missing models (Review, Favorite), incomplete API endpoints, security hardening, performance optimization, and comprehensive error handling. The implementation prioritizes security, scalability, and maintainability while maintaining the existing application structure.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Frontend Layer                           │
│  ┌──────────────────┐  ┌──────────────────┐                │
│  │  React/Inertia   │  │  Livewire Admin  │                │
│  │  Public Pages    │  │  Dashboard       │                │
│  └──────────────────┘  └──────────────────┘                │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Middleware Layer                           │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐      │
│  │   Auth   │ │  Admin   │ │   CSRF   │ │   Rate   │      │
│  │          │ │  Check   │ │  Verify  │ │  Limit   │      │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Controller Layer                           │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐       │
│  │ Marketplace  │ │   Plugin     │ │   Review     │       │
│  │ Controller   │ │  Controller  │ │  Controller  │       │
│  └──────────────┘ └──────────────┘ └──────────────┘       │
│  ┌──────────────┐ ┌──────────────┐                        │
│  │  Favorite    │ │    Admin     │                        │
│  │  Controller  │ │  Controller  │                        │
│  └──────────────┘ └──────────────┘                        │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Service Layer                              │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐       │
│  │   Rating     │ │    File      │ │    Cache     │       │
│  │   Service    │ │   Upload     │ │   Service    │       │
│  └──────────────┘ └──────────────┘ └──────────────┘       │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                     Model Layer                              │
│  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐  │
│  │ Plugin │ │ Review │ │Favorite│ │Category│ │  User  │  │
│  └────────┘ └────────┘ └────────┘ └────────┘ └────────┘  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Data Layer                                 │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐       │
│  │   MySQL      │ │    Redis     │ │   Storage    │       │
│  │   Database   │ │    Cache     │ │   (S3/Local) │       │
│  └──────────────┘ └──────────────┘ └──────────────┘       │
└─────────────────────────────────────────────────────────────┘
```

### Design Principles

1. **Separation of Concerns**: Controllers handle HTTP, services handle business logic, models handle data
2. **Security First**: All inputs validated, outputs escaped, authentication/authorization enforced
3. **Performance Optimization**: Caching at multiple layers, eager loading, database indexing
4. **Maintainability**: Clear naming conventions, comprehensive documentation, consistent patterns
5. **Testability**: Dependency injection, factory patterns, isolated business logic

## Components and Interfaces

### 1. Model Layer

#### Review Model

```php
namespace App\Models;

class Review extends Model
{
    protected $fillable = ['plugin_id', 'user_id', 'rating', 'comment'];
    
    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relationships
    public function plugin(): BelongsTo
    public function user(): BelongsTo
    
    // Business Logic
    protected static function booted(): void
    {
        // After create/update/delete, recalculate plugin rating
        static::created(fn($review) => $review->plugin->recalculateRating());
        static::updated(fn($review) => $review->plugin->recalculateRating());
        static::deleted(fn($review) => $review->plugin->recalculateRating());
    }
}
```

#### Favorite Model

```php
namespace App\Models;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'plugin_id'];
    
    // Relationships
    public function user(): BelongsTo
    public function plugin(): BelongsTo
    
    // Scopes
    public function scopeForUser($query, $userId): Builder
    public function scopeForPlugin($query, $pluginId): Builder
}
```

#### Enhanced Plugin Model

```php
namespace App\Models;

class Plugin extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id', 'category_id', 'name', 'slug', 'description', 
        'logo_path', 'version', 'status', 'compatibility', 
        'requirements', 'license_type', 'downloads', 
        'rating_avg', 'rating_count', 'published_at'
    ];
    
    protected $casts = [
        'requirements' => 'array',
        'published_at' => 'datetime',
        'rating_avg' => 'decimal:2',
    ];
    
    // Relationships
    public function user(): BelongsTo
    public function category(): BelongsTo
    public function reviews(): HasMany
    public function favorites(): HasMany
    
    // Business Logic
    public function incrementDownload(): void
    public function recalculateRating(): void
    public function isOwnedBy(User $user): bool
    public function isFavoritedBy(?User $user): bool
    
    // Scopes
    public function scopeActive($query): Builder
    public function scopePending($query): Builder
    public function scopePublished($query): Builder
}
```

### 2. Controller Layer

#### PluginController

Handles plugin CRUD operations for authenticated users.

```php
namespace App\Http\Controllers;

class PluginController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    
    // GET /plugins - List user's plugins
    public function index(): Response
    
    // GET /plugins/create - Show create form
    public function create(): Response
    
    // POST /plugins - Store new plugin
    public function store(StorePluginRequest $request): RedirectResponse
    
    // GET /plugins/{plugin}/edit - Show edit form
    public function edit(Plugin $plugin): Response
    
    // PUT /plugins/{plugin} - Update plugin
    public function update(UpdatePluginRequest $request, Plugin $plugin): RedirectResponse
    
    // DELETE /plugins/{plugin} - Soft delete plugin
    public function destroy(Plugin $plugin): RedirectResponse
}
```

#### ReviewController

Handles review submission and management.

```php
namespace App\Http\Controllers;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    
    // POST /plugins/{plugin}/reviews - Create or update review
    public function store(StoreReviewRequest $request, Plugin $plugin): RedirectResponse
    
    // DELETE /reviews/{review} - Delete user's review
    public function destroy(Review $review): RedirectResponse
}
```

#### FavoriteController

Handles favorite toggle operations.

```php
namespace App\Http\Controllers;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    
    // POST /plugins/{plugin}/favorite - Toggle favorite
    public function toggle(Plugin $plugin): JsonResponse
    
    // GET /favorites - List user's favorites
    public function index(): Response
}
```

#### AdminController

Handles administrative operations.

```php
namespace App\Http\Controllers\Admin;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }
    
    // GET /admin/dashboard - Show dashboard with stats
    public function dashboard(): Response
    
    // GET /admin/plugins - List all plugins with filters
    public function plugins(Request $request): Response
    
    // PUT /admin/plugins/{plugin}/approve - Approve pending plugin
    public function approvePlugin(Plugin $plugin): RedirectResponse
    
    // PUT /admin/plugins/{plugin}/reject - Reject pending plugin
    public function rejectPlugin(Plugin $plugin): RedirectResponse
    
    // PUT /admin/plugins/{plugin}/toggle-status - Activate/deactivate
    public function toggleStatus(Plugin $plugin): RedirectResponse
    
    // DELETE /admin/reviews/{review} - Delete inappropriate review
    public function deleteReview(Review $review): RedirectResponse
    
    // Category management
    public function categories(): Response
    public function storeCategory(Request $request): RedirectResponse
    public function updateCategory(Request $request, Category $category): RedirectResponse
    public function deleteCategory(Category $category): RedirectResponse
}
```

### 3. Request Validation Layer

#### StorePluginRequest

```php
namespace App\Http\Requests;

class StorePluginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:plugins,name',
            'description' => 'required|string|max:5000',
            'category_id' => 'required|exists:categories,id',
            'version' => 'required|string|max:50',
            'compatibility' => 'required|string|max:100',
            'license_type' => 'required|string|in:MIT,GPL,Apache,Proprietary',
            'requirements' => 'required|array',
            'requirements.php' => 'required|string',
            'requirements.laravel' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048|dimensions:min_width=200,min_height=200',
        ];
    }
}
```

#### UpdatePluginRequest

```php
namespace App\Http\Requests;

class UpdatePluginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->plugin->isOwnedBy(auth()->user());
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:plugins,name,' . $this->plugin->id,
            'description' => 'required|string|max:5000',
            'category_id' => 'required|exists:categories,id',
            'version' => 'required|string|max:50',
            'compatibility' => 'required|string|max:100',
            'license_type' => 'required|string|in:MIT,GPL,Apache,Proprietary',
            'requirements' => 'required|array',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048|dimensions:min_width=200,min_height=200',
        ];
    }
}
```

#### StoreReviewRequest

```php
namespace App\Http\Requests;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User cannot review their own plugin
        return auth()->check() && 
               $this->route('plugin')->user_id !== auth()->id();
    }
    
    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}
```

### 4. Middleware Layer

#### EnsureUserIsAdmin

```php
namespace App\Http\Middleware;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Unauthorized access to admin area');
        }
        
        return $next($request);
    }
}
```

#### EnsureEmailIsVerified

Laravel's built-in `verified` middleware will be used for email verification.

### 5. Service Layer

#### FileUploadService

Handles file uploads with validation and storage.

```php
namespace App\Services;

class FileUploadService
{
    public function uploadPluginLogo(UploadedFile $file, ?string $oldPath = null): string
    {
        // Delete old file if exists
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
        
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->extension();
        
        // Store file
        $path = $file->storeAs('plugins/logos', $filename, 'public');
        
        return $path;
    }
    
    public function deletePluginLogo(string $path): void
    {
        Storage::disk('public')->delete($path);
    }
}
```

#### CacheService

Centralized cache management.

```php
namespace App\Services;

class CacheService
{
    const PLUGIN_LIST_TTL = 300; // 5 minutes
    const PLUGIN_DETAIL_TTL = 600; // 10 minutes
    const CATEGORY_LIST_TTL = 3600; // 1 hour
    
    public function getPluginList(string $cacheKey, callable $callback): mixed
    {
        return Cache::remember($cacheKey, self::PLUGIN_LIST_TTL, $callback);
    }
    
    public function getPluginDetail(int $pluginId, callable $callback): mixed
    {
        return Cache::remember("plugin.{$pluginId}", self::PLUGIN_DETAIL_TTL, $callback);
    }
    
    public function invalidatePlugin(int $pluginId): void
    {
        Cache::forget("plugin.{$pluginId}");
        Cache::tags(['plugins'])->flush();
    }
    
    public function getCategoryList(callable $callback): mixed
    {
        return Cache::remember('categories.all', self::CATEGORY_LIST_TTL, $callback);
    }
}
```

### 6. Database Layer

#### Migration Enhancements

Add indexes for performance:

```php
// Add to existing migrations or create new migration
Schema::table('plugins', function (Blueprint $table) {
    $table->index('slug');
    $table->index('status');
    $table->index('category_id');
    $table->index('user_id');
    $table->index('published_at');
    $table->index(['status', 'published_at']); // Composite for active published plugins
});

Schema::table('reviews', function (Blueprint $table) {
    $table->index('plugin_id');
    $table->index('user_id');
    $table->index(['plugin_id', 'user_id']); // Composite for finding user's review
});

Schema::table('favorites', function (Blueprint $table) {
    $table->index('user_id');
    $table->index('plugin_id');
    // unique constraint already exists on [user_id, plugin_id]
});
```

Add soft deletes to plugins:

```php
Schema::table('plugins', function (Blueprint $table) {
    $table->softDeletes();
});
```

Add admin flag to users:

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_admin')->default(false);
});
```

## Data Models

### Entity Relationship Diagram

```mermaid
erDiagram
    User ||--o{ Plugin : creates
    User ||--o{ Review : writes
    User ||--o{ Favorite : has
    Category ||--o{ Plugin : contains
    Plugin ||--o{ Review : receives
    Plugin ||--o{ Favorite : has
    
    User {
        bigint id PK
        string name
        string email UK
        string password
        timestamp email_verified_at
        boolean is_admin
        timestamps
    }
    
    Category {
        bigint id PK
        string name
        string slug UK
        timestamps
    }
    
    Plugin {
        bigint id PK
        bigint user_id FK
        bigint category_id FK
        string name
        string slug UK
        text description
        string logo_path
        string version
        string status
        string compatibility
        json requirements
        string license_type
        integer downloads
        decimal rating_avg
        integer rating_count
        timestamp published_at
        timestamps
        softDeletes
    }
    
    Review {
        bigint id PK
        bigint plugin_id FK
        bigint user_id FK
        integer rating
        text comment
        timestamps
    }
    
    Favorite {
        bigint id PK
        bigint user_id FK
        bigint plugin_id FK
        timestamps
        unique(user_id, plugin_id)
    }
```

### Data Validation Rules

**Plugin**:
- name: required, string, max 255 chars, unique
- slug: auto-generated from name, unique
- description: required, string, max 5000 chars
- version: required, string, max 50 chars
- status: enum (pending, active, inactive, rejected)
- compatibility: required, string, max 100 chars
- requirements: required, JSON object with php and laravel keys
- license_type: required, enum (MIT, GPL, Apache, Proprietary)
- logo_path: nullable, valid image path
- downloads: integer, default 0
- rating_avg: decimal(3,2), default 0.00
- rating_count: integer, default 0

**Review**:
- plugin_id: required, exists in plugins table
- user_id: required, exists in users table
- rating: required, integer, between 1 and 5
- comment: nullable, string, max 1000 chars
- Constraint: unique combination of (plugin_id, user_id)

**Favorite**:
- plugin_id: required, exists in plugins table
- user_id: required, exists in users table
- Constraint: unique combination of (plugin_id, user_id)

**Category**:
- name: required, string, max 255 chars
- slug: required, string, unique

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Rating Recalculation on Review Changes

*For any* plugin with reviews, when a review is created, updated, or deleted, the plugin's average rating and review count should accurately reflect all current reviews.

**Validates: Requirements 1.3, 1.4**

### Property 2: Rating Value Validation

*For any* review submission, if the rating value is not an integer between 1 and 5 (inclusive), the system should reject the submission with a validation error.

**Validates: Requirements 1.5**

### Property 3: Duplicate Favorite Prevention

*For any* user and plugin combination, attempting to create a favorite when one already exists should either prevent the duplicate or be idempotent (no duplicate created).

**Validates: Requirements 1.6**

### Property 4: Soft Delete Preservation

*For any* plugin that is deleted, the plugin record should remain in the database with a deleted_at timestamp, and should not appear in normal queries but should be retrievable with soft delete queries.

**Validates: Requirements 1.7**

### Property 5: Plugin Creation Pending Status

*For any* valid plugin creation request by an authenticated user, the created plugin should have status set to "pending".

**Validates: Requirements 2.1**

### Property 6: Plugin Ownership Authorization

*For any* plugin update or delete request, if the authenticated user is not the plugin owner and not an admin, the request should be rejected with a 403 Forbidden response.

**Validates: Requirements 2.2**

### Property 7: File Upload Validation

*For any* file upload for plugin logo, if the file does not meet requirements (type: jpeg/png/jpg/svg, size: ≤2MB, dimensions: ≥200x200), the upload should be rejected with a descriptive validation error.

**Validates: Requirements 2.4**

### Property 8: Unique Slug Generation

*For any* plugin name, the system should generate a unique slug, and if a slug collision occurs, the system should append a suffix to ensure uniqueness.

**Validates: Requirements 2.5**

### Property 9: Validation Error Structure

*For any* invalid request, the validation error response should include field-specific error messages in a structured format (JSON with field names as keys).

**Validates: Requirements 2.6**

### Property 10: XSS Input Sanitization

*For any* user input containing HTML/JavaScript tags (e.g., `<script>`, `<img onerror>`), the system should sanitize or escape the input so that it cannot execute as code when displayed.

**Validates: Requirements 2.7**

### Property 11: Review Creation

*For any* authenticated user and plugin (where user is not the plugin owner), submitting a valid review should create a review record associated with that user and plugin.

**Validates: Requirements 3.1**

### Property 12: Review Update Idempotency

*For any* user who has already reviewed a plugin, submitting another review for the same plugin should update the existing review rather than creating a duplicate.

**Validates: Requirements 3.2**

### Property 13: Review Comment Length Validation

*For any* review submission with a comment exceeding 1000 characters, the system should reject the submission with a validation error.

**Validates: Requirements 3.6**

### Property 14: Self-Review Prevention

*For any* plugin, if the authenticated user is the plugin owner, attempting to submit a review should be rejected with an authorization error.

**Validates: Requirements 3.8**

### Property 15: Favorite Creation

*For any* authenticated user and plugin, favoriting the plugin should create a favorite record linking the user and plugin.

**Validates: Requirements 4.1**

### Property 16: Favorite Removal

*For any* existing favorite, unfavoriting should remove the favorite record from the database.

**Validates: Requirements 4.3**

### Property 17: User Favorites Listing

*For any* authenticated user, requesting their favorites list should return all and only the plugins they have favorited.

**Validates: Requirements 4.4**

### Property 18: Favorite Status Indication

*For any* plugin and authenticated user, the plugin data should correctly indicate whether the user has favorited it (true if favorite exists, false otherwise).

**Validates: Requirements 4.5**

### Property 19: Cascade Delete Favorites

*For any* plugin with associated favorites, when the plugin is deleted, all favorite records referencing that plugin should also be deleted.

**Validates: Requirements 4.7**

### Property 20: Admin Dashboard Statistics

*For any* admin dashboard request, the displayed statistics (total plugins, users, reviews, downloads) should accurately reflect the current database counts.

**Validates: Requirements 5.2**

### Property 21: Admin Plugin Filtering

*For any* plugin status filter applied by admin, the returned plugin list should contain only plugins matching that status.

**Validates: Requirements 5.3**

### Property 22: Admin Plugin Approval

*For any* pending plugin, when an admin approves it, the plugin status should change to "active" and published_at should be set to the current timestamp.

**Validates: Requirements 5.4**

### Property 23: Admin Plugin Status Toggle

*For any* plugin, when an admin toggles its status between active and inactive, the plugin status should change accordingly.

**Validates: Requirements 5.5**

### Property 24: Admin Review Deletion

*For any* review, an admin should be able to delete it regardless of ownership, and the deletion should succeed and recalculate plugin ratings.

**Validates: Requirements 5.6**

### Property 25: Admin Action Logging

*For any* admin action (approve, reject, toggle status), the system should create a log entry containing the action type, plugin ID, admin user ID, and timestamp.

**Validates: Requirements 5.8**

### Property 26: Admin Search Functionality

*For any* search query in admin plugin management, the results should include all plugins whose name or description contains the search term (case-insensitive).

**Validates: Requirements 5.9**

### Property 27: Admin Category Management

*For any* category CRUD operation by admin (create, update, delete), the operation should succeed and the category data should be correctly persisted or removed.

**Validates: Requirements 5.10**

### Property 28: Authentication Middleware Protection

*For any* protected route, if the request is not authenticated, the system should redirect to login or return 401 Unauthorized.

**Validates: Requirements 6.2**

### Property 29: Admin Authorization Middleware

*For any* admin route, if the authenticated user does not have admin role, the system should return 403 Forbidden.

**Validates: Requirements 6.3**

### Property 30: CSRF Protection

*For any* state-changing request (POST, PUT, DELETE), if the CSRF token is missing or invalid, the system should reject the request with a 419 error.

**Validates: Requirements 6.6**

### Property 31: Rate Limiting

*For any* rate-limited endpoint, if the request rate exceeds the configured limit within the time window, subsequent requests should be rejected with 429 Too Many Requests.

**Validates: Requirements 6.8**

### Property 32: Required Field Validation

*For any* request with required fields, if any required field is missing or empty, the system should reject the request with a validation error specifying which fields are required.

**Validates: Requirements 7.6**

### Property 33: Type Validation

*For any* request field with a specific type requirement, if the provided value does not match the expected type, the system should reject the request with a type validation error.

**Validates: Requirements 7.7**

### Property 34: Pagination

*For any* large result set query, the system should return paginated results with a maximum page size, and provide pagination metadata (current page, total pages, total items).

**Validates: Requirements 8.7**

### Property 35: Database Exception Handling

*For any* database operation that throws an exception (connection failure, constraint violation), the system should catch the exception and return a user-friendly error response without exposing database details.

**Validates: Requirements 10.1**

### Property 36: File Upload Exception Handling

*For any* file upload operation that throws an exception (disk full, permission denied), the system should catch the exception and return a user-friendly error response.

**Validates: Requirements 10.2**

### Property 37: Error Logging

*For any* error or exception that occurs, the system should log the error with context information (timestamp, user ID, request path, error message, stack trace) to the configured log storage.

**Validates: Requirements 10.3**

### Property 38: User-Friendly Error Messages

*For any* error response to the client, the message should be user-friendly and not expose sensitive technical details (no stack traces, database queries, or file paths).

**Validates: Requirements 10.4**

### Property 39: No Sensitive Data in Errors

*For any* error response, the response should not contain sensitive information such as database credentials, API keys, or internal system paths.

**Validates: Requirements 10.5**

### Property 40: Plugin List Caching

*For any* plugin list query with the same parameters, the second request within 5 minutes should be served from cache and should return the same data as the first request.

**Validates: Requirements 11.1**

### Property 41: Category List Caching

*For any* category list query, the second request within 1 hour should be served from cache and should return the same data as the first request.

**Validates: Requirements 11.2**

### Property 42: Plugin Detail Caching

*For any* individual plugin detail query, the second request within 10 minutes should be served from cache and should return the same data as the first request.

**Validates: Requirements 11.3**

### Property 43: Cache Invalidation on Plugin Update

*For any* plugin update operation, the plugin's detail cache and any list caches containing that plugin should be invalidated immediately.

**Validates: Requirements 11.4**

### Property 44: Cache Invalidation on Review Addition

*For any* review creation for a plugin, the plugin's detail cache should be invalidated immediately to reflect the new rating.

**Validates: Requirements 11.5**

### Property 45: Plugin Detail Display Completeness

*For any* plugin detail page, the displayed information should include all required fields: name, description, version, compatibility, requirements, average rating, and review count.

**Validates: Requirements 12.2**

### Property 46: Rating Display

*For any* plugin detail page, the displayed average rating and review count should match the plugin's current rating_avg and rating_count values.

**Validates: Requirements 12.3**

### Property 47: Review Pagination

*For any* plugin with more reviews than the page size, the reviews should be paginated and the pagination controls should allow navigation through all reviews.

**Validates: Requirements 12.4**


## Error Handling

### Error Handling Strategy

The application implements a comprehensive error handling strategy with multiple layers:

1. **Validation Layer**: Form requests catch invalid input before processing
2. **Authorization Layer**: Middleware and policies prevent unauthorized access
3. **Business Logic Layer**: Service classes handle domain-specific errors
4. **Data Layer**: Model events and database transactions ensure data integrity
5. **Global Handler**: Laravel's exception handler catches all unhandled exceptions

### Exception Types and Handling

#### Validation Exceptions

```php
// Handled automatically by Laravel FormRequest
// Returns 422 Unprocessable Entity with field-specific errors
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."],
        "rating": ["The rating must be between 1 and 5."]
    }
}
```

#### Authorization Exceptions

```php
// Handled by middleware and policies
// Returns 403 Forbidden
{
    "message": "This action is unauthorized."
}
```

#### Authentication Exceptions

```php
// Handled by auth middleware
// Returns 401 Unauthorized or redirects to login
{
    "message": "Unauthenticated."
}
```

#### Database Exceptions

```php
// Caught in controllers and services
try {
    $plugin->save();
} catch (QueryException $e) {
    Log::error('Database error saving plugin', [
        'plugin_id' => $plugin->id,
        'error' => $e->getMessage(),
        'user_id' => auth()->id(),
    ]);
    
    return back()->with('error', 'Unable to save plugin. Please try again.');
}
```

#### File Upload Exceptions

```php
// Caught in file upload service
try {
    $path = $file->store('plugins/logos', 'public');
} catch (Exception $e) {
    Log::error('File upload failed', [
        'filename' => $file->getClientOriginalName(),
        'error' => $e->getMessage(),
        'user_id' => auth()->id(),
    ]);
    
    throw new FileUploadException('Unable to upload file. Please try again.');
}
```

#### Rate Limit Exceptions

```php
// Handled by throttle middleware
// Returns 429 Too Many Requests
{
    "message": "Too many requests. Please try again later."
}
```

### Logging Strategy

All errors are logged with contextual information:

```php
Log::error('Error message', [
    'user_id' => auth()->id(),
    'request_path' => request()->path(),
    'request_method' => request()->method(),
    'ip_address' => request()->ip(),
    'error' => $exception->getMessage(),
    'trace' => $exception->getTraceAsString(),
]);
```

Admin actions are logged for audit trail:

```php
Log::info('Admin action', [
    'admin_id' => auth()->id(),
    'action' => 'approve_plugin',
    'plugin_id' => $plugin->id,
    'timestamp' => now(),
]);
```

### User-Friendly Error Messages

All error responses to users are sanitized to prevent information leakage:

- **Production**: Generic messages without technical details
- **Development**: Detailed error messages with stack traces (debug mode only)
- **Never expose**: Database credentials, file paths, internal system details

## Testing Strategy

### Overview

The testing strategy employs a dual approach combining unit tests for specific scenarios and property-based tests for universal correctness guarantees. This ensures both concrete examples work correctly and general properties hold across all inputs.

### Testing Framework

- **Framework**: PHPUnit (Laravel's default testing framework)
- **Property-Based Testing**: Use PHPUnit with custom generators or a library like `eris/eris` for PHP property-based testing
- **Database**: In-memory SQLite for fast test execution
- **Factories**: Laravel factories for generating test data

### Property-Based Testing Configuration

Each property test must:
- Run minimum 100 iterations with randomized inputs
- Reference the design document property number
- Use the tag format: `@test Feature: production-ready-marketplace, Property {N}: {property_text}`

Example:

```php
/**
 * @test
 * Feature: production-ready-marketplace, Property 1: Rating Recalculation on Review Changes
 */
public function test_rating_recalculates_on_review_changes()
{
    // Run 100 iterations with random data
    for ($i = 0; $i < 100; $i++) {
        $plugin = Plugin::factory()->create();
        $reviews = Review::factory()->count(rand(1, 10))->create([
            'plugin_id' => $plugin->id,
            'rating' => rand(1, 5),
        ]);
        
        $expectedAvg = $reviews->avg('rating');
        $expectedCount = $reviews->count();
        
        $plugin->refresh();
        
        $this->assertEquals($expectedAvg, $plugin->rating_avg, '', 0.01);
        $this->assertEquals($expectedCount, $plugin->rating_count);
    }
}
```

### Unit Testing Strategy

Unit tests focus on:
- **Specific examples**: Concrete scenarios that demonstrate correct behavior
- **Edge cases**: Boundary conditions (empty lists, maximum values, null inputs)
- **Error conditions**: Invalid inputs, authorization failures, constraint violations
- **Integration points**: Interactions between components

Unit tests should NOT duplicate property test coverage. If a property test validates a universal rule, unit tests should focus on specific edge cases or integration scenarios.

### Test Organization

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── PluginTest.php
│   │   ├── ReviewTest.php
│   │   └── FavoriteTest.php
│   ├── Services/
│   │   ├── FileUploadServiceTest.php
│   │   └── CacheServiceTest.php
│   └── Requests/
│       ├── StorePluginRequestTest.php
│       └── StoreReviewRequestTest.php
├── Feature/
│   ├── PluginManagementTest.php
│   ├── ReviewSystemTest.php
│   ├── FavoriteSystemTest.php
│   ├── AdminDashboardTest.php
│   └── AuthenticationTest.php
└── Property/
    ├── RatingCalculationPropertyTest.php
    ├── ValidationPropertyTest.php
    ├── AuthorizationPropertyTest.php
    ├── CachingPropertyTest.php
    └── DataIntegrityPropertyTest.php
```

### Coverage Requirements

- **Critical paths**: Minimum 80% code coverage
- **Controllers**: All endpoints tested with valid and invalid inputs
- **Models**: All business logic methods tested
- **Services**: All public methods tested
- **Validation**: All validation rules tested
- **Authorization**: All policies and middleware tested

### Test Data Generation

Use Laravel factories for consistent test data:

```php
// PluginFactory
Plugin::factory()->create([
    'status' => 'active',
    'rating_avg' => 4.5,
    'rating_count' => 10,
]);

// ReviewFactory
Review::factory()->create([
    'rating' => 5,
    'comment' => 'Excellent plugin!',
]);

// FavoriteFactory
Favorite::factory()->create([
    'user_id' => $user->id,
    'plugin_id' => $plugin->id,
]);
```

### Property Test Examples

#### Property 1: Rating Recalculation

```php
/**
 * @test
 * Feature: production-ready-marketplace, Property 1: Rating Recalculation on Review Changes
 */
public function test_rating_recalculates_correctly_for_all_review_operations()
{
    for ($i = 0; $i < 100; $i++) {
        $plugin = Plugin::factory()->create();
        $reviewCount = rand(1, 20);
        
        // Create random reviews
        $reviews = Review::factory()->count($reviewCount)->create([
            'plugin_id' => $plugin->id,
            'rating' => rand(1, 5),
        ]);
        
        $plugin->refresh();
        $expectedAvg = round($reviews->avg('rating'), 2);
        
        $this->assertEquals($expectedAvg, $plugin->rating_avg);
        $this->assertEquals($reviewCount, $plugin->rating_count);
        
        // Update a random review
        $review = $reviews->random();
        $review->update(['rating' => rand(1, 5)]);
        
        $plugin->refresh();
        $expectedAvg = round($plugin->reviews()->avg('rating'), 2);
        
        $this->assertEquals($expectedAvg, $plugin->rating_avg);
        
        // Delete a random review
        $reviews->random()->delete();
        
        $plugin->refresh();
        $expectedCount = $plugin->reviews()->count();
        $expectedAvg = $expectedCount > 0 
            ? round($plugin->reviews()->avg('rating'), 2) 
            : 0;
        
        $this->assertEquals($expectedAvg, $plugin->rating_avg);
        $this->assertEquals($expectedCount, $plugin->rating_count);
    }
}
```

#### Property 3: Duplicate Favorite Prevention

```php
/**
 * @test
 * Feature: production-ready-marketplace, Property 3: Duplicate Favorite Prevention
 */
public function test_duplicate_favorites_are_prevented_for_all_user_plugin_combinations()
{
    for ($i = 0; $i < 100; $i++) {
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
        try {
            Favorite::create([
                'user_id' => $user->id,
                'plugin_id' => $plugin->id,
            ]);
            
            // If no exception, verify only one exists
            $count = Favorite::where('user_id', $user->id)
                ->where('plugin_id', $plugin->id)
                ->count();
            
            $this->assertEquals(1, $count, 'Duplicate favorite was created');
        } catch (QueryException $e) {
            // Database constraint prevented duplicate - this is correct
            $this->assertStringContainsString('unique', strtolower($e->getMessage()));
        }
    }
}
```

#### Property 10: XSS Input Sanitization

```php
/**
 * @test
 * Feature: production-ready-marketplace, Property 10: XSS Input Sanitization
 */
public function test_xss_payloads_are_sanitized_for_all_user_inputs()
{
    $xssPayloads = [
        '<script>alert("XSS")</script>',
        '<img src=x onerror=alert("XSS")>',
        '<svg onload=alert("XSS")>',
        'javascript:alert("XSS")',
        '<iframe src="javascript:alert(\'XSS\')">',
    ];
    
    for ($i = 0; $i < 100; $i++) {
        $user = User::factory()->create();
        $payload = $xssPayloads[array_rand($xssPayloads)];
        
        // Test plugin description
        $response = $this->actingAs($user)->post('/plugins', [
            'name' => 'Test Plugin',
            'description' => $payload,
            'category_id' => Category::factory()->create()->id,
            'version' => '1.0.0',
            'compatibility' => 'Laravel 11',
            'license_type' => 'MIT',
            'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
        ]);
        
        if ($response->status() === 302) {
            $plugin = Plugin::latest()->first();
            $rendered = view('market.show', ['plugin' => $plugin])->render();
            
            // Verify payload is escaped and cannot execute
            $this->assertStringNotContainsString('<script>', $rendered);
            $this->assertStringNotContainsString('onerror=', $rendered);
            $this->assertStringNotContainsString('javascript:', $rendered);
        }
        
        // Test review comment
        $plugin = Plugin::factory()->create();
        $response = $this->actingAs($user)->post("/plugins/{$plugin->id}/reviews", [
            'rating' => rand(1, 5),
            'comment' => $payload,
        ]);
        
        if ($response->status() === 302) {
            $review = Review::latest()->first();
            $rendered = view('market.show', [
                'plugin' => $plugin,
                'reviews' => collect([$review]),
            ])->render();
            
            // Verify payload is escaped
            $this->assertStringNotContainsString('<script>', $rendered);
            $this->assertStringNotContainsString('onerror=', $rendered);
        }
    }
}
```

### Integration Testing

Integration tests verify interactions between components:

```php
public function test_complete_plugin_lifecycle()
{
    // User creates plugin
    $user = User::factory()->create();
    $response = $this->actingAs($user)->post('/plugins', [
        'name' => 'Test Plugin',
        'description' => 'A test plugin',
        'category_id' => Category::factory()->create()->id,
        'version' => '1.0.0',
        'compatibility' => 'Laravel 11',
        'license_type' => 'MIT',
        'requirements' => ['php' => '8.2', 'laravel' => '11.0'],
    ]);
    
    $plugin = Plugin::latest()->first();
    $this->assertEquals('pending', $plugin->status);
    
    // Admin approves plugin
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->put("/admin/plugins/{$plugin->id}/approve");
    
    $plugin->refresh();
    $this->assertEquals('active', $plugin->status);
    $this->assertNotNull($plugin->published_at);
    
    // Another user reviews plugin
    $reviewer = User::factory()->create();
    $this->actingAs($reviewer)->post("/plugins/{$plugin->id}/reviews", [
        'rating' => 5,
        'comment' => 'Great plugin!',
    ]);
    
    $plugin->refresh();
    $this->assertEquals(5.0, $plugin->rating_avg);
    $this->assertEquals(1, $plugin->rating_count);
    
    // User favorites plugin
    $this->actingAs($reviewer)->post("/plugins/{$plugin->id}/favorite");
    
    $this->assertDatabaseHas('favorites', [
        'user_id' => $reviewer->id,
        'plugin_id' => $plugin->id,
    ]);
}
```

### Continuous Integration

Tests should run automatically on:
- Every commit (via Git hooks)
- Every pull request (via CI/CD pipeline)
- Before deployment (via deployment pipeline)

CI configuration should:
- Run all tests in parallel when possible
- Generate code coverage reports
- Fail builds if coverage drops below 80%
- Run static analysis (PHPStan, Psalm)
- Run code style checks (PHP CS Fixer)

## Implementation Notes

### Development Workflow

1. **Phase 1: Models and Migrations**
   - Create Review and Favorite models
   - Add migrations for new tables and indexes
   - Implement model relationships and business logic
   - Add soft deletes to Plugin model

2. **Phase 2: Validation and Security**
   - Create FormRequest classes for validation
   - Implement middleware for authentication and authorization
   - Add CSRF protection and rate limiting
   - Implement XSS sanitization

3. **Phase 3: Controllers and Routes**
   - Implement PluginController CRUD operations
   - Implement ReviewController
   - Implement FavoriteController
   - Implement AdminController

4. **Phase 4: Services**
   - Implement FileUploadService
   - Implement CacheService
   - Add error handling and logging

5. **Phase 5: Frontend**
   - Create Market/Show.jsx page
   - Implement review submission form
   - Implement favorite toggle button
   - Add loading states and error handling

6. **Phase 6: Performance Optimization**
   - Add database indexes
   - Implement caching strategy
   - Configure Redis for cache and sessions
   - Optimize queries with eager loading

7. **Phase 7: Testing**
   - Write property-based tests
   - Write unit tests
   - Write integration tests
   - Achieve 80% code coverage

8. **Phase 8: Production Configuration**
   - Configure environment variables
   - Set up error logging
   - Configure file storage (S3)
   - Set up queue workers

### Security Checklist

- [ ] All routes protected with authentication middleware
- [ ] Admin routes protected with admin middleware
- [ ] CSRF protection enabled on all state-changing requests
- [ ] Rate limiting configured on authentication endpoints
- [ ] File uploads validated for type, size, and dimensions
- [ ] All user input sanitized to prevent XSS
- [ ] SQL injection prevented via Eloquent ORM
- [ ] Sensitive data not exposed in error messages
- [ ] Secure session configuration (httpOnly, secure, sameSite)
- [ ] Content Security Policy headers configured
- [ ] Debug mode disabled in production
- [ ] Environment variables used for sensitive configuration

### Performance Checklist

- [ ] Database indexes on foreign keys and frequently queried columns
- [ ] Eager loading to prevent N+1 queries
- [ ] Redis configured for cache and sessions
- [ ] Plugin listings cached for 5 minutes
- [ ] Category lists cached for 1 hour
- [ ] Plugin details cached for 10 minutes
- [ ] Cache invalidation on data changes
- [ ] Pagination on large result sets
- [ ] Opcache enabled for PHP
- [ ] CDN configured for static assets
- [ ] Database connection pooling configured
- [ ] Queue workers for background jobs

### Deployment Considerations

- **Database Migrations**: Run migrations before deploying new code
- **Cache Clearing**: Clear cache after deployment
- **Queue Workers**: Restart queue workers after deployment
- **Zero Downtime**: Use Laravel Vapor or similar for zero-downtime deployments
- **Rollback Plan**: Keep previous version available for quick rollback
- **Monitoring**: Set up error tracking (Sentry, Bugsnag) and performance monitoring (New Relic, Scout)
- **Backups**: Automated daily database backups with point-in-time recovery
- **Health Checks**: Implement health check endpoints for load balancer monitoring
