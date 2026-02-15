<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Marketplace
Route::prefix('market')->name('market.')->group(function () {
    Route::get('/', [MarketplaceController::class, 'index'])->name('index');
    Route::get('/{plugin:slug}', [MarketplaceController::class, 'show'])->name('show');
    Route::post('/{plugin:slug}/download', [MarketplaceController::class, 'download'])->name('download');
});

// Documentation
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', [DocumentationController::class, 'index'])->name('index');
    Route::get('/{documentation:slug}', [DocumentationController::class, 'show'])->name('show');
});

// Dashboard
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Plugin Management
    Route::resource('plugins', PluginController::class);
    
    // Reviews
    Route::post('/plugins/{plugin}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    
    // Favorites
    Route::post('/plugins/{plugin}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    
    // Reports
    Route::post('/plugins/{plugin}/report', [ReportController::class, 'store'])->name('reports.store');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/plugins', [AdminController::class, 'plugins'])->name('plugins');
    Route::put('/plugins/{plugin}/approve', [AdminController::class, 'approvePlugin'])->name('plugins.approve');
    Route::put('/plugins/{plugin}/reject', [AdminController::class, 'rejectPlugin'])->name('plugins.reject');
    Route::put('/plugins/{plugin}/toggle-status', [AdminController::class, 'toggleStatus'])->name('plugins.toggle-status');
    Route::delete('/reviews/{review}', [AdminController::class, 'deleteReview'])->name('reviews.delete');
    
    // Category Management
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.delete');
}); 

require __DIR__.'/auth.php';
