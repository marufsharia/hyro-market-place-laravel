<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Models\Category;
use App\Models\Review;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class MarketplaceController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index(Request $request)
    {
        try {
            // Build cache key based on filters and page
            $cacheKey = 'plugins.list.' . md5(json_encode([
                'search' => $request->search,
                'category' => $request->category,
                'page' => $request->page ?? 1,
            ]));

            // Cache plugin listings for 5 minutes
            $plugins = $this->cacheService->getPluginList($cacheKey, function () use ($request) {
                $query = Plugin::query()->with('category', 'user')->where('status', 'active');

                if ($request->has('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                          ->orWhere('description', 'like', '%' . $search . '%');
                    });
                }

                if ($request->has('category') && $request->category !== 'All') {
                    $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
                }

                return $query->latest('published_at')->paginate(12)->withQueryString();
            });
            
            // Add favorite status for authenticated users (not cached as it's user-specific)
            if (auth()->check()) {
                $plugins->getCollection()->transform(function ($plugin) {
                    $plugin->is_favorited = $plugin->isFavoritedBy(auth()->user());
                    return $plugin;
                });
            }

            $categories = $this->cacheService->getCategoryList(fn() => Category::all());

            return inertia('Market/Index', [
                'plugins' => $plugins,
                'categories' => $categories,
                'filters' => $request->only(['search', 'category'])
            ]);
        } catch (QueryException $e) {
            Log::error('Database error loading marketplace', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_path' => $request->path(),
                'ip_address' => $request->ip(),
            ]);
            
            return inertia('Market/Index', [
                'plugins' => [],
                'categories' => [],
                'filters' => $request->only(['search', 'category']),
                'error' => 'Unable to load plugins. Please try again later.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading marketplace', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_path' => $request->path(),
                'ip_address' => $request->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return inertia('Market/Index', [
                'plugins' => [],
                'categories' => [],
                'filters' => $request->only(['search', 'category']),
                'error' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }

    public function show(Plugin $plugin)
    {
        try {
            // Ensure we only show active plugins publicly unless owner
            if ($plugin->status !== 'active' && (!auth()->check() || auth()->id() !== $plugin->user_id)) {
                abort(404);
            }

            // Cache plugin details for 10 minutes
            $pluginData = $this->cacheService->getPluginDetail($plugin->id, function () use ($plugin) {
                $plugin->load([
                    'user', 
                    'category', 
                    'reviews' => fn($q) => $q->with('user')->latest()->paginate(10)
                ]);
                
                return $plugin;
            });
            
            // Add favorite status (not cached as it's user-specific)
            $pluginData->is_favorited = $pluginData->isFavoritedBy(auth()->user());
            
            // Check if user has already reviewed (not cached as it's user-specific)
            $userReview = null;
            if (auth()->check()) {
                $userReview = $pluginData->reviews()->where('user_id', auth()->id())->first();
            }
            
            return inertia('Market/Show', [
                'plugin' => $pluginData,
                'userReview' => $userReview,
            ]);
        } catch (QueryException $e) {
            Log::error('Database error loading plugin details', [
                'plugin_id' => $plugin->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            abort(500, 'Unable to load plugin details. Please try again later.');
        } catch (\Exception $e) {
            Log::error('Error loading plugin details', [
                'plugin_id' => $plugin->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            abort(500, 'An unexpected error occurred. Please try again later.');
        }
    }

    public function download(Plugin $plugin)
    {
        try {
            $plugin->incrementDownload();
            
            // Invalidate cache
            $this->cacheService->invalidatePlugin($plugin->id);
            
            // In production, this would redirect to a zip file or GitHub release
            return Redirect::away('https://github.com/marufsharia/hyro');
        } catch (QueryException $e) {
            Log::error('Database error incrementing download count', [
                'plugin_id' => $plugin->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            // Still allow download even if count increment fails
            return Redirect::away('https://github.com/marufsharia/hyro');
        } catch (\Exception $e) {
            Log::error('Error processing download', [
                'plugin_id' => $plugin->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Still allow download even if there's an error
            return Redirect::away('https://github.com/marufsharia/hyro');
        }
    }
}