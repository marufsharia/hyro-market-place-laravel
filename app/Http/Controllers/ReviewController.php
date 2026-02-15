<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Models\Review;
use App\Http\Requests\StoreReviewRequest;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class ReviewController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->middleware(['auth', 'verified']);
        $this->cacheService = $cacheService;
    }

    public function store(StoreReviewRequest $request, Plugin $plugin)
    {
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['plugin_id'] = $plugin->id;

            // Sanitize comment input to prevent XSS
            if (!empty($data['comment'])) {
                $data['comment'] = strip_tags($data['comment']);
            }

            // Update or create review (user can only have one review per plugin)
            $review = Review::updateOrCreate(
                ['user_id' => auth()->id(), 'plugin_id' => $plugin->id],
                $data
            );

            // Invalidate plugin cache
            $this->cacheService->invalidatePlugin($plugin->id);

            return back()->with('success', 'Review submitted successfully.');
        } catch (QueryException $e) {
            Log::error('Database error saving review', [
                'plugin_id' => $plugin->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);
            
            return back()->with('error', 'Unable to save review. Please try again.');
        } catch (\Exception $e) {
            Log::error('Error saving review', [
                'plugin_id' => $plugin->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function destroy(Review $review)
    {
        try {
            // Ensure user owns the review
            if ($review->user_id !== auth()->id()) {
                abort(403);
            }

            $pluginId = $review->plugin_id;
            $review->delete();

            // Invalidate plugin cache
            $this->cacheService->invalidatePlugin($pluginId);

            return back()->with('success', 'Review deleted successfully.');
        } catch (QueryException $e) {
            Log::error('Database error deleting review', [
                'review_id' => $review->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);
            
            return back()->with('error', 'Unable to delete review. Please try again.');
        } catch (\Exception $e) {
            Log::error('Error deleting review', [
                'review_id' => $review->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
}
