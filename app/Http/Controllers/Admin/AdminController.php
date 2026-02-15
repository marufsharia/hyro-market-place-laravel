<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Models\User;
use App\Models\Review;
use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Inertia\Inertia;

class AdminController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->middleware(['auth', 'admin']);
        $this->cacheService = $cacheService;
    }

    /**
     * Display the admin dashboard with statistics
     */
    public function dashboard()
    {
        try {
            $stats = [
                'total_plugins' => Plugin::count(),
                'total_users' => User::count(),
                'total_reviews' => Review::count(),
                'total_downloads' => Plugin::sum('downloads'),
                'pending_plugins' => Plugin::pending()->count(),
                'active_plugins' => Plugin::active()->count(),
            ];

            return Inertia::render('Admin/Dashboard', [
                'stats' => $stats,
            ]);
        } catch (QueryException $e) {
            Log::error('Database error loading admin dashboard', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);
            
            return Inertia::render('Admin/Dashboard', [
                'stats' => [
                    'total_plugins' => 0,
                    'total_users' => 0,
                    'total_reviews' => 0,
                    'total_downloads' => 0,
                    'pending_plugins' => 0,
                    'active_plugins' => 0,
                ],
                'error' => 'Unable to load dashboard statistics. Please try again later.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading admin dashboard', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Inertia::render('Admin/Dashboard', [
                'stats' => [
                    'total_plugins' => 0,
                    'total_users' => 0,
                    'total_reviews' => 0,
                    'total_downloads' => 0,
                    'pending_plugins' => 0,
                    'active_plugins' => 0,
                ],
                'error' => 'An unexpected error occurred. Please try again later.',
            ]);
        }
    }

    /**
     * List all plugins with filtering and search
     */
    public function plugins(Request $request)
    {
        try {
            $query = Plugin::with(['user', 'category']);

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search by name or description
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $plugins = $query->latest()->paginate(15);

            return Inertia::render('Admin/Plugins', [
                'plugins' => $plugins,
                'filters' => [
                    'status' => $request->status ?? 'all',
                    'search' => $request->search ?? '',
                ],
            ]);
        } catch (QueryException $e) {
            Log::error('Database error loading admin plugins', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);
            
            return Inertia::render('Admin/Plugins', [
                'plugins' => [],
                'filters' => [
                    'status' => $request->status ?? 'all',
                    'search' => $request->search ?? '',
                ],
                'error' => 'Unable to load plugins. Please try again later.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading admin plugins', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Inertia::render('Admin/Plugins', [
                'plugins' => [],
                'filters' => [
                    'status' => $request->status ?? 'all',
                    'search' => $request->search ?? '',
                ],
                'error' => 'An unexpected error occurred. Please try again later.',
            ]);
        }
    }

    /**
     * Approve a pending plugin
     */
    public function approvePlugin(Plugin $plugin)
    {
        try {
            $plugin->update([
                'status' => 'active',
                'published_at' => now(),
            ]);

            // Log admin action
            Log::channel('admin_actions')->info('Admin action: Plugin approved', [
                'admin_id' => auth()->id(),
                'action' => 'approve_plugin',
                'plugin_id' => $plugin->id,
                'timestamp' => now(),
            ]);

            // Invalidate plugin cache
            $this->cacheService->invalidatePlugin($plugin->id);

            return back()->with('success', 'Plugin approved successfully.');
        } catch (QueryException $e) {
            Log::error('Database error approving plugin', [
                'plugin_id' => $plugin->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);

            return back()->with('error', 'Unable to approve plugin. Please try again.');
        } catch (\Exception $e) {
            Log::error('Error approving plugin', [
                'plugin_id' => $plugin->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Reject a pending plugin
     */
    public function rejectPlugin(Plugin $plugin)
    {
        try {
            $plugin->update([
                'status' => 'rejected',
            ]);

            // Log admin action
            Log::channel('admin_actions')->info('Admin action: Plugin rejected', [
                'admin_id' => auth()->id(),
                'action' => 'reject_plugin',
                'plugin_id' => $plugin->id,
                'timestamp' => now(),
            ]);

            // Invalidate plugin cache
            $this->cacheService->invalidatePlugin($plugin->id);

            return back()->with('success', 'Plugin rejected.');
        } catch (QueryException $e) {
            Log::error('Database error rejecting plugin', [
                'plugin_id' => $plugin->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);

            return back()->with('error', 'Unable to reject plugin. Please try again.');
        } catch (\Exception $e) {
            Log::error('Error rejecting plugin', [
                'plugin_id' => $plugin->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Toggle plugin status between active and inactive
     */
    public function toggleStatus(Plugin $plugin)
    {
        try {
            $newStatus = $plugin->status === 'active' ? 'inactive' : 'active';
            
            $plugin->update([
                'status' => $newStatus,
            ]);

            // Log admin action
            Log::channel('admin_actions')->info('Admin action: Plugin status toggled', [
                'admin_id' => auth()->id(),
                'action' => 'toggle_status',
                'plugin_id' => $plugin->id,
                'old_status' => $plugin->status,
                'new_status' => $newStatus,
                'timestamp' => now(),
            ]);

            // Invalidate plugin cache
            $this->cacheService->invalidatePlugin($plugin->id);

            return back()->with('success', "Plugin status changed to {$newStatus}.");
        } catch (QueryException $e) {
            Log::error('Database error toggling plugin status', [
                'plugin_id' => $plugin->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);

            return back()->with('error', 'Unable to change plugin status. Please try again.');
        } catch (\Exception $e) {
            Log::error('Error toggling plugin status', [
                'plugin_id' => $plugin->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Delete a review (admin privilege)
     */
    public function deleteReview(Review $review)
    {
        try {
            $pluginId = $review->plugin_id;
            
            $review->delete();

            // Log admin action
            Log::channel('admin_actions')->info('Admin action: Review deleted', [
                'admin_id' => auth()->id(),
                'action' => 'delete_review',
                'review_id' => $review->id,
                'plugin_id' => $pluginId,
                'timestamp' => now(),
            ]);

            // Invalidate plugin cache (rating will be recalculated by model event)
            $this->cacheService->invalidatePlugin($pluginId);

            return back()->with('success', 'Review deleted successfully.');
        } catch (QueryException $e) {
            Log::error('Database error deleting review', [
                'review_id' => $review->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);

            return back()->with('error', 'Unable to delete review. Please try again.');
        } catch (\Exception $e) {
            Log::error('Error deleting review', [
                'review_id' => $review->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * List all categories
     */
    public function categories()
    {
        try {
            $categories = Category::withCount('plugins')->get();

            return Inertia::render('Admin/Categories', [
                'categories' => $categories,
            ]);
        } catch (QueryException $e) {
            Log::error('Database error loading categories', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);
            
            return Inertia::render('Admin/Categories', [
                'categories' => [],
                'error' => 'Unable to load categories. Please try again later.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading categories', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Inertia::render('Admin/Categories', [
                'categories' => [],
                'error' => 'An unexpected error occurred. Please try again later.',
            ]);
        }
    }

    /**
     * Store a new category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'required|string|max:255|unique:categories,slug',
        ]);

        try {
            $category = Category::create($request->only(['name', 'slug']));

            // Log admin action
            Log::channel('admin_actions')->info('Admin action: Category created', [
                'admin_id' => auth()->id(),
                'action' => 'create_category',
                'category_id' => $category->id,
                'timestamp' => now(),
            ]);

            // Invalidate category cache
            $this->cacheService->invalidateCategories();

            return back()->with('success', 'Category created successfully.');
        } catch (QueryException $e) {
            Log::error('Database error creating category', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);

            return back()->with('error', 'Unable to create category. Please try again.');
        } catch (\Exception $e) {
            Log::error('Error creating category', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Update an existing category
     */
    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
        ]);

        try {
            $category->update($request->only(['name', 'slug']));

            // Log admin action
            Log::channel('admin_actions')->info('Admin action: Category updated', [
                'admin_id' => auth()->id(),
                'action' => 'update_category',
                'category_id' => $category->id,
                'timestamp' => now(),
            ]);

            // Invalidate category cache
            $this->cacheService->invalidateCategories();

            return back()->with('success', 'Category updated successfully.');
        } catch (QueryException $e) {
            Log::error('Database error updating category', [
                'category_id' => $category->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);

            return back()->with('error', 'Unable to update category. Please try again.');
        } catch (\Exception $e) {
            Log::error('Error updating category', [
                'category_id' => $category->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Delete a category
     */
    public function deleteCategory(Category $category)
    {
        try {
            // Check if category has plugins
            if ($category->plugins()->count() > 0) {
                return back()->with('error', 'Cannot delete category with existing plugins.');
            }

            $category->delete();

            // Log admin action
            Log::channel('admin_actions')->info('Admin action: Category deleted', [
                'admin_id' => auth()->id(),
                'action' => 'delete_category',
                'category_id' => $category->id,
                'timestamp' => now(),
            ]);

            // Invalidate category cache
            $this->cacheService->invalidateCategories();

            return back()->with('success', 'Category deleted successfully.');
        } catch (QueryException $e) {
            Log::error('Database error deleting category', [
                'category_id' => $category->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);

            return back()->with('error', 'Unable to delete category. Please try again.');
        } catch (\Exception $e) {
            Log::error('Error deleting category', [
                'category_id' => $category->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
}
