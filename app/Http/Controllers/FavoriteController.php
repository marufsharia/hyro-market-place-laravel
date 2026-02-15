<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Models\Favorite;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function toggle(Plugin $plugin)
    {
        try {
            $favorite = Favorite::where('user_id', auth()->id())
                ->where('plugin_id', $plugin->id)
                ->first();

            if ($favorite) {
                $favorite->delete();
                return response()->json([
                    'favorited' => false,
                    'message' => 'Plugin removed from favorites.',
                ]);
            }

            Favorite::create([
                'user_id' => auth()->id(),
                'plugin_id' => $plugin->id,
            ]);

            return response()->json([
                'favorited' => true,
                'message' => 'Plugin added to favorites.',
            ]);
        } catch (QueryException $e) {
            Log::error('Database error toggling favorite', [
                'plugin_id' => $plugin->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);
            
            return response()->json([
                'error' => 'Unable to update favorites. Please try again.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error toggling favorite', [
                'plugin_id' => $plugin->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    public function index()
    {
        try {
            $favorites = auth()->user()->favorites()
                ->with(['plugin' => function ($query) {
                    $query->with(['category', 'user']);
                }])
                ->latest()
                ->paginate(12);

            return Inertia::render('Favorites/Index', [
                'favorites' => $favorites,
            ]);
        } catch (QueryException $e) {
            Log::error('Database error loading favorites', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
            ]);
            
            return Inertia::render('Favorites/Index', [
                'favorites' => [],
                'error' => 'Unable to load favorites. Please try again later.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading favorites', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_path' => request()->path(),
                'ip_address' => request()->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Inertia::render('Favorites/Index', [
                'favorites' => [],
                'error' => 'An unexpected error occurred. Please try again later.',
            ]);
        }
    }
}
