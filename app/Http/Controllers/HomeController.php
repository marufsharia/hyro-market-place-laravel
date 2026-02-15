<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Show the application dashboard / landing page.
     */
    public function index()
    {
        // Fetch stats for the landing page
        $totalPlugins = Plugin::where('status', 'active')->count();
        $totalDownloads = Plugin::sum('downloads'); // Sum of all downloads across all plugins

        // Fetch "Featured" plugins (e.g., top 4 by downloads)
        $featuredPlugins = Plugin::where('status', 'active')
            ->with('category', 'user')
            ->orderBy('downloads', 'desc')
            ->take(4)
            ->get()
            ->map(function ($plugin) {
                return [
                    'id' => $plugin->id,
                    'name' => $plugin->name,
                    'slug' => $plugin->slug,
                    'description' => $plugin->description,
                    'logo_path' => $plugin->logo_path,
                    'downloads' => $plugin->downloads,
                    'rating_avg' => $plugin->rating_avg,
                ];
            });

        return Inertia::render('Home', [
            'stats' => [
                'plugins' => $totalPlugins,
                'downloads' => number_format($totalDownloads),
            ],
            'featuredPlugins' => $featuredPlugins,
        ]);
    }
}