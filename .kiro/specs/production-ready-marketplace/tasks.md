# Implementation Plan: Production-Ready Marketplace

## Overview

This implementation plan transforms the Hyro Marketplace from a basic prototype into a production-ready Laravel 11 application. The approach follows a phased implementation strategy: starting with data models and migrations, then building up through validation, controllers, services, frontend components, performance optimization, and comprehensive testing. Each task builds incrementally, ensuring that code is integrated and functional at every step.

## Tasks

- [x] 1. Create Review and Favorite models with relationships
  - [x] 1.1 Create Review model with database migration
    - Create `app/Models/Review.php` with fillable fields: plugin_id, user_id, rating, comment
    - Create migration for reviews table with foreign keys to plugins and users
    - Add unique constraint on (plugin_id, user_id) to prevent duplicate reviews
    - Add indexes on plugin_id and user_id columns
    - _Requirements: 1.1, 1.2, 3.2_
  
  - [x] 1.2 Create Favorite model with database migration
    - Create `app/Models/Favorite.php` with fillable fields: user_id, plugin_id
    - Create migration for favorites table with foreign keys
    - Add unique constraint on (user_id, plugin_id) to prevent duplicates
    - Add composite index on (user_id, plugin_id)
    - Add cascade delete on plugin deletion
    - _Requirements: 1.2, 4.2, 4.6, 4.7_
  
  - [x] 1.3 Add model relationships and scopes
    - Add belongsTo relationships in Review model (plugin, user)
    - Add belongsTo relationships in Favorite model (plugin, user)
    - Add hasMany relationships in Plugin model (reviews, favorites)
    - Add hasMany relationships in User model (reviews, favorites)
    - Add scopes to Favorite model: forUser(), forPlugin()
    - _Requirements: 1.1, 1.2_
  
  - [x] 1.4 Write property test for Review model relationships
    - **Property 1: Rating Recalculation on Review Changes**
    - **Validates: Requirements 1.3, 1.4**
  
  - [x] 1.5 Write property test for Favorite model constraints
    - **Property 3: Duplicate Favorite Prevention**
    - **Validates: Requirements 1.6**

- [x] 2. Implement rating calculation system
  - [x] 2.1 Add rating fields to Plugin model
    - Add rating_avg (decimal) and rating_count (integer) to plugins table migration
    - Update Plugin model fillable and casts arrays
    - _Requirements: 1.3, 1.4_
  
  - [x] 2.2 Implement rating recalculation logic in Plugin model
    - Create `recalculateRating()` method in Plugin model
    - Calculate average from all reviews and update rating_avg and rating_count
    - Handle edge case when no reviews exist (set to 0)
    - _Requirements: 1.3, 1.4_
  
  - [x] 2.3 Add model event observers to Review model
    - Implement booted() method in Review model
    - Add created event to trigger plugin rating recalculation
    - Add updated event to trigger plugin rating recalculation
    - Add deleted event to trigger plugin rating recalculation
    - _Requirements: 1.3, 1.4_
  
  - [x] 2.4 Write property test for rating recalculation
    - **Property 1: Rating Recalculation on Review Changes**
    - Test with 100 iterations of random review operations (create, update, delete)
    - **Validates: Requirements 1.3, 1.4**

- [x] 3. Enhance Plugin model with soft deletes and business logic
  - [x] 3.1 Add soft deletes to Plugin model
    - Add SoftDeletes trait to Plugin model
    - Create migration to add deleted_at column to plugins table
    - Add softDeletes index
    - _Requirements: 1.7_
  
  - [x] 3.2 Add business logic methods to Plugin model
    - Implement `incrementDownload()` method to increment downloads counter
    - Implement `isOwnedBy(User $user)` method to check ownership
    - Implement `isFavoritedBy(?User $user)` method to check if user favorited
    - Add scopes: active(), pending(), published()
    - _Requirements: 2.2, 4.5_
  
  - [x] 3.3 Add slug generation and admin flag
    - Add slug auto-generation in Plugin model using Str::slug()
    - Handle slug collisions by appending numeric suffix
    - Create migration to add is_admin boolean to users table
    - _Requirements: 2.5, 6.3_
  
  - [x] 3.4 Write property test for soft deletes
    - **Property 4: Soft Delete Preservation**
    - **Validates: Requirements 1.7**
  
  - [x] 3.5 Write property test for slug generation
    - **Property 8: Unique Slug Generation**
    - **Validates: Requirements 2.5**

- [-] 4. Create Form Request validators
  - [x] 4.1 Create StorePluginRequest validator
    - Create `app/Http/Requests/StorePluginRequest.php`
    - Implement authorize() to check authentication
    - Implement rules() with validation for: name (required, unique), description, category_id, version, compatibility, license_type, requirements (array), logo (image validation)
    - Add custom error messages for better UX
    - _Requirements: 2.1, 2.4, 2.6, 7.1, 7.4, 7.6, 7.7_
  
  - [x] 4.2 Create UpdatePluginRequest validator
    - Create `app/Http/Requests/UpdatePluginRequest.php`
    - Implement authorize() to check ownership using isOwnedBy()
    - Implement rules() similar to StorePluginRequest but with unique rule excluding current plugin
    - _Requirements: 2.2, 2.6, 6.7, 7.2_
  
  - [x] 4.3 Create StoreReviewRequest validator
    - Create `app/Http/Requests/StoreReviewRequest.php`
    - Implement authorize() to prevent self-reviews (check plugin owner != current user)
    - Implement rules() with: rating (required, integer, min:1, max:5), comment (nullable, string, max:1000)
    - _Requirements: 1.5, 3.6, 3.8, 7.3_
  
  - [-] 4.4 Write property test for validation rules
    - **Property 2: Rating Value Validation**
    - **Property 7: File Upload Validation**
    - **Property 9: Validation Error Structure**
    - **Property 13: Review Comment Length Validation**
    - **Validates: Requirements 1.5, 2.4, 2.6, 3.6**
  
  - [-] 4.5 Write property test for authorization
    - **Property 6: Plugin Ownership Authorization**
    - **Property 14: Self-Review Prevention**
    - **Validates: Requirements 2.2, 3.8**

- [~] 5. Implement middleware for authentication and authorization
  - [x] 5.1 Create EnsureUserIsAdmin middleware
    - Create `app/Http/Middleware/EnsureUserIsAdmin.php`
    - Check if user is authenticated and has is_admin flag
    - Return 403 Forbidden if not admin
    - Register middleware in Kernel
    - _Requirements: 6.3, 6.4_
  
  - [x] 5.2 Configure rate limiting
    - Add rate limiting to authentication routes (login, register) in RouteServiceProvider
    - Configure throttle middleware with appropriate limits (e.g., 5 attempts per minute)
    - _Requirements: 6.8, 9.2_
  
  - [~] 5.3 Write property test for admin authorization
    - **Property 29: Admin Authorization Middleware**
    - **Validates: Requirements 6.3**
  
  - [~] 5.4 Write property test for rate limiting
    - **Property 31: Rate Limiting**
    - **Validates: Requirements 6.8**

- [~] 6. Create FileUploadService for handling file uploads
  - [x] 6.1 Implement FileUploadService
    - Create `app/Services/FileUploadService.php`
    - Implement `uploadPluginLogo()` method with file validation, unique filename generation, and storage
    - Implement `deletePluginLogo()` method to remove old files
    - Add error handling with try-catch and logging
    - _Requirements: 2.4, 9.1, 9.8, 9.9, 10.2_
  
  - [~] 6.2 Write unit tests for FileUploadService
    - Test successful upload with valid file
    - Test rejection of invalid file types
    - Test rejection of oversized files
    - Test old file deletion when uploading new file
    - _Requirements: 2.4, 9.1_

- [~] 7. Implement PluginController for CRUD operations
  - [x] 7.1 Create PluginController with index and create methods
    - Create `app/Http/Controllers/PluginController.php`
    - Add auth and verified middleware in constructor
    - Implement index() to list user's plugins with pagination
    - Implement create() to show plugin creation form
    - _Requirements: 2.1, 6.2_
  
  - [x] 7.2 Implement store method for plugin creation
    - Implement store(StorePluginRequest) method
    - Handle logo upload using FileUploadService
    - Generate unique slug from name
    - Set status to 'pending' by default
    - Set user_id to authenticated user
    - Sanitize description input to prevent XSS
    - Return redirect with success message
    - _Requirements: 2.1, 2.5, 2.7_
  
  - [x] 7.3 Implement edit, update, and destroy methods
    - Implement edit(Plugin) to show edit form with authorization check
    - Implement update(UpdatePluginRequest, Plugin) with logo upload handling
    - Implement destroy(Plugin) to soft delete plugin with authorization check
    - Add error handling with try-catch blocks
    - _Requirements: 2.2, 2.3, 6.7_
  
  - [~] 7.4 Write property test for plugin creation
    - **Property 5: Plugin Creation Pending Status**
    - **Property 10: XSS Input Sanitization**
    - **Validates: Requirements 2.1, 2.7**
  
  - [~] 7.5 Write feature tests for PluginController
    - Test authenticated user can create plugin
    - Test unauthenticated user cannot access plugin routes
    - Test user can only edit/delete their own plugins
    - Test plugin soft delete functionality
    - _Requirements: 2.1, 2.2, 2.3, 6.2_

- [-] 8. Implement ReviewController for review management
  - [x] 8.1 Create ReviewController with store method
    - Create `app/Http/Controllers/ReviewController.php`
    - Add auth and verified middleware in constructor
    - Implement store(StoreReviewRequest, Plugin) method
    - Check if user already reviewed plugin (updateOrCreate pattern)
    - Sanitize comment input to prevent XSS
    - Return redirect with success message
    - _Requirements: 3.1, 3.2, 3.3_
  
  - [x] 8.2 Implement destroy method for review deletion
    - Implement destroy(Review) method with authorization check (user owns review)
    - Delete review and trigger rating recalculation
    - Return redirect with success message
    - _Requirements: 3.4_
  
  - [~] 8.3 Write property test for review operations
    - **Property 11: Review Creation**
    - **Property 12: Review Update Idempotency**
    - **Validates: Requirements 3.1, 3.2**
  
  - [~] 8.4 Write feature tests for ReviewController
    - Test authenticated user can submit review
    - Test user cannot review their own plugin
    - Test duplicate review updates existing review
    - Test review deletion recalculates rating
    - _Requirements: 3.1, 3.2, 3.4, 3.8_

- [~] 9. Implement FavoriteController for favorite management
  - [x] 9.1 Create FavoriteController with toggle method
    - Create `app/Http/Controllers/FavoriteController.php`
    - Add auth and verified middleware in constructor
    - Implement toggle(Plugin) method to create or delete favorite
    - Use firstOrCreate and delete pattern for toggle behavior
    - Return JSON response with favorite status
    - _Requirements: 4.1, 4.2, 4.3_
  
  - [x] 9.2 Implement index method for listing favorites
    - Implement index() method to list user's favorited plugins
    - Eager load plugin relationships to prevent N+1 queries
    - Return Inertia response with paginated favorites
    - _Requirements: 4.4_
  
  - [~] 9.3 Write property test for favorite operations
    - **Property 15: Favorite Creation**
    - **Property 16: Favorite Removal**
    - **Property 17: User Favorites Listing**
    - **Property 18: Favorite Status Indication**
    - **Validates: Requirements 4.1, 4.3, 4.4, 4.5**
  
  - [~] 9.4 Write feature tests for FavoriteController
    - Test authenticated user can favorite plugin
    - Test duplicate favorite is prevented
    - Test unfavorite removes favorite
    - Test user can list their favorites
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [~] 10. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [-] 11. Implement AdminController for admin operations
  - [x] 11.1 Create AdminController with dashboard method
    - Create `app/Http/Controllers/Admin/AdminController.php`
    - Add auth and admin middleware in constructor
    - Implement dashboard() method to calculate and display statistics
    - Query counts for: total plugins, users, reviews, total downloads
    - Return Inertia/Livewire response with statistics
    - _Requirements: 5.1, 5.2_
  
  - [x] 11.2 Implement plugin management methods
    - Implement plugins(Request) to list all plugins with status filtering
    - Add search functionality by name or description
    - Implement pagination for plugin list
    - Eager load relationships to prevent N+1 queries
    - _Requirements: 5.3, 5.9_
  
  - [x] 11.3 Implement plugin approval and status methods
    - Implement approvePlugin(Plugin) to set status to 'active' and set published_at
    - Implement rejectPlugin(Plugin) to set status to 'rejected'
    - Implement toggleStatus(Plugin) to toggle between active/inactive
    - Log all admin actions with admin_id, action type, plugin_id, and timestamp
    - Invalidate plugin cache after status changes
    - _Requirements: 5.4, 5.5, 5.8_
  
  - [x] 11.4 Implement review and category management methods
    - Implement deleteReview(Review) to delete any review (admin privilege)
    - Implement categories() to list all categories
    - Implement storeCategory(Request) to create new category
    - Implement updateCategory(Request, Category) to update category
    - Implement deleteCategory(Category) to delete category
    - _Requirements: 5.6, 5.10_
  
  - [~] 11.5 Write property tests for admin operations
    - **Property 20: Admin Dashboard Statistics**
    - **Property 21: Admin Plugin Filtering**
    - **Property 22: Admin Plugin Approval**
    - **Property 23: Admin Plugin Status Toggle**
    - **Property 24: Admin Review Deletion**
    - **Property 25: Admin Action Logging**
    - **Property 26: Admin Search Functionality**
    - **Property 27: Admin Category Management**
    - **Validates: Requirements 5.2, 5.3, 5.4, 5.5, 5.6, 5.8, 5.9, 5.10**
  
  - [~] 11.6 Write feature tests for AdminController
    - Test only admin can access admin routes
    - Test non-admin gets 403 on admin routes
    - Test admin can approve/reject plugins
    - Test admin can delete any review
    - Test admin can manage categories
    - _Requirements: 5.1, 5.4, 5.5, 5.6, 5.10, 6.3, 6.4_

- [~] 12. Create CacheService for performance optimization
  - [x] 12.1 Implement CacheService with caching methods
    - Create `app/Services/CacheService.php`
    - Define cache TTL constants: PLUGIN_LIST_TTL (300s), PLUGIN_DETAIL_TTL (600s), CATEGORY_LIST_TTL (3600s)
    - Implement getPluginList() with Cache::remember()
    - Implement getPluginDetail() with Cache::remember()
    - Implement getCategoryList() with Cache::remember()
    - Implement invalidatePlugin() to clear plugin-specific caches
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_
  
  - [x] 12.2 Integrate caching into controllers
    - Update MarketplaceController to use CacheService for plugin listings
    - Update plugin detail views to use CacheService
    - Update category listings to use CacheService
    - Add cache invalidation calls in PluginController update/delete methods
    - Add cache invalidation calls in ReviewController store/delete methods
    - Add cache invalidation calls in AdminController status change methods
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_
  
  - [~] 12.3 Write property tests for caching behavior
    - **Property 40: Plugin List Caching**
    - **Property 41: Category List Caching**
    - **Property 42: Plugin Detail Caching**
    - **Property 43: Cache Invalidation on Plugin Update**
    - **Property 44: Cache Invalidation on Review Addition**
    - **Validates: Requirements 11.1, 11.2, 11.3, 11.4, 11.5**

- [x] 13. Add database indexes for performance
  - [x] 13.1 Create migration for database indexes
    - Create migration to add indexes to plugins table: slug, status, category_id, user_id, published_at
    - Add composite index on plugins: (status, published_at)
    - Add indexes to reviews table: plugin_id, user_id
    - Add composite index on reviews: (plugin_id, user_id)
    - Add indexes to favorites table: user_id, plugin_id (unique constraint already exists)
    - _Requirements: 8.1, 8.2, 8.3_
  
  - [x] 13.2 Implement eager loading to prevent N+1 queries
    - Update all controller methods that load plugins to eager load: user, category, reviews
    - Update review queries to eager load: user
    - Update favorite queries to eager load: plugin
    - Use with() method consistently across all queries
    - _Requirements: 8.4_

- [-] 14. Implement frontend Market/Show.jsx page
  - [x] 14.1 Create Market/Show.jsx component structure
    - Create `resources/js/Pages/Market/Show.jsx`
    - Display plugin details: name, description, version, compatibility, requirements, license
    - Display average rating and review count with star visualization
    - Display plugin logo with fallback
    - Add download button that increments download counter
    - _Requirements: 12.1, 12.2, 12.3_
  
  - [x] 14.2 Implement review display and submission
    - Display paginated list of reviews with user name, rating, comment, and timestamp
    - Create review submission form with rating selector (1-5 stars) and comment textarea
    - Add client-side validation for rating (required) and comment (max 1000 chars)
    - Handle form submission with Inertia post request
    - Display loading state during submission
    - Display success/error messages after submission
    - _Requirements: 12.4, 12.5, 12.9_
  
  - [x] 14.3 Implement favorite toggle button
    - Add favorite button with heart icon
    - Show filled heart if user has favorited, outline if not
    - Handle toggle with AJAX request to FavoriteController
    - Update UI optimistically and revert on error
    - Display loading state during request
    - _Requirements: 12.6_
  
  - [x] 14.4 Add error handling and XSS prevention
    - Display error messages when operations fail
    - Add loading states for all asynchronous operations
    - Sanitize and escape all user-generated content (reviews, descriptions)
    - Use React's built-in XSS protection (JSX escaping)
    - _Requirements: 12.7, 12.8, 12.10_
  
  - [~] 14.5 Write integration tests for frontend
    - Test plugin detail page displays all required information
    - Test review submission creates review
    - Test favorite toggle works correctly
    - Test error messages display on failures
    - _Requirements: 12.2, 12.5, 12.6, 12.8_

- [~] 15. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [-] 16. Implement comprehensive error handling
  - [x] 16.1 Add error handling to controllers
    - Wrap database operations in try-catch blocks in all controllers
    - Catch QueryException and return user-friendly error messages
    - Catch file upload exceptions in PluginController
    - Log all errors with context: user_id, request_path, error message, stack trace
    - Return appropriate HTTP status codes (422, 403, 500)
    - _Requirements: 10.1, 10.2, 10.3, 10.4_
  
  - [x] 16.2 Configure error logging
    - Update `config/logging.php` to log errors to daily files
    - Configure log channels for different environments (local, production)
    - Add context to all log entries: user_id, IP address, request details
    - Implement admin action logging in AdminController
    - _Requirements: 10.3, 10.6_
  
  - [x] 16.3 Customize exception handler
    - Update `app/Exceptions/Handler.php` to customize error responses
    - Ensure production errors don't expose sensitive information
    - Return JSON responses for API requests
    - Return Inertia responses for web requests
    - _Requirements: 10.4, 10.5_
  
  - [~] 16.4 Write property tests for error handling
    - **Property 35: Database Exception Handling**
    - **Property 36: File Upload Exception Handling**
    - **Property 37: Error Logging**
    - **Property 38: User-Friendly Error Messages**
    - **Property 39: No Sensitive Data in Errors**
    - **Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5**

- [x] 17. Configure production environment settings
  - [x] 17.1 Update environment configuration files
    - Update `.env.example` with all required environment variables
    - Configure Redis for cache and session storage (CACHE_DRIVER=redis, SESSION_DRIVER=redis)
    - Configure queue driver (QUEUE_CONNECTION=redis)
    - Configure file storage driver (FILESYSTEM_DISK=s3 for production)
    - Set APP_DEBUG=false for production
    - Configure database connection pooling settings
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6, 13.10_
  
  - [x] 17.2 Configure security settings
    - Update `config/session.php` with secure settings: httpOnly=true, secure=true, sameSite='lax'
    - Configure trusted proxies in `app/Http/Middleware/TrustProxies.php`
    - Configure CORS settings in `config/cors.php`
    - Add Content Security Policy headers in middleware
    - _Requirements: 9.7, 9.10, 13.7, 13.8, 13.9_
  
  - [x] 17.3 Configure performance settings
    - Enable opcache in `php.ini` for production
    - Configure Redis connection settings in `config/database.php`
    - Set up queue worker configuration in `config/queue.php`
    - Configure CDN URL for static assets in `.env`
    - _Requirements: 11.9, 11.10_

- [x] 18. Create database seeders for testing and development
  - [x] 18.1 Create model factories
    - Create `database/factories/ReviewFactory.php` with random rating and comment
    - Create `database/factories/FavoriteFactory.php`
    - Update PluginFactory to include all new fields (rating_avg, rating_count, published_at)
    - Update UserFactory to include is_admin flag
    - _Requirements: 1.8, 8.5_
  
  - [x] 18.2 Create database seeders
    - Create `database/seeders/CategorySeeder.php` with common plugin categories
    - Create `database/seeders/PluginSeeder.php` with sample plugins
    - Create `database/seeders/ReviewSeeder.php` with sample reviews
    - Create `database/seeders/UserSeeder.php` with admin and regular users
    - Update DatabaseSeeder to call all seeders in correct order
    - _Requirements: 8.5_

- [~] 19. Write comprehensive property-based tests
  - [~] 19.1 Write authentication and authorization property tests
    - **Property 28: Authentication Middleware Protection**
    - **Property 30: CSRF Protection**
    - **Validates: Requirements 6.2, 6.6**
  
  - [~] 19.2 Write validation property tests
    - **Property 32: Required Field Validation**
    - **Property 33: Type Validation**
    - **Validates: Requirements 7.6, 7.7**
  
  - [~] 19.3 Write pagination property test
    - **Property 34: Pagination**
    - **Validates: Requirements 8.7**
  
  - [~] 19.4 Write frontend property tests
    - **Property 45: Plugin Detail Display Completeness**
    - **Property 46: Rating Display**
    - **Property 47: Review Pagination**
    - **Validates: Requirements 12.2, 12.3, 12.4**

- [~] 20. Write integration tests for complete workflows
  - [~] 20.1 Write plugin lifecycle integration test
    - Test complete flow: user creates plugin → admin approves → user reviews → user favorites
    - Verify all state changes and side effects occur correctly
    - _Requirements: 2.1, 3.1, 4.1, 5.4_
  
  - [~] 20.2 Write admin workflow integration test
    - Test admin dashboard statistics accuracy
    - Test admin plugin management (approve, reject, toggle status)
    - Test admin review deletion
    - Test admin category management
    - _Requirements: 5.2, 5.4, 5.5, 5.6, 5.10_
  
  - [~] 20.3 Write caching integration test
    - Test cache population on first request
    - Test cache hit on second request
    - Test cache invalidation on data changes
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_

- [~] 21. Final checkpoint and code review
  - Run all tests and ensure 80% code coverage
  - Review security checklist and verify all items completed
  - Review performance checklist and verify all optimizations applied
  - Run static analysis tools (PHPStan, Psalm)
  - Run code style checks (PHP CS Fixer)
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at key milestones
- Property tests validate universal correctness properties with minimum 100 iterations
- Unit tests validate specific examples and edge cases
- Integration tests validate complete workflows across multiple components
- The implementation follows Laravel best practices with clear separation of concerns
- Security and performance are prioritized throughout the implementation
- All user input is validated and sanitized to prevent XSS and SQL injection
- Caching is implemented at multiple layers for optimal performance
- Comprehensive error handling ensures graceful degradation and helpful error messages
