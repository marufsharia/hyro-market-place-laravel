<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Plugin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'name', 'slug', 'description', 'logo_path',
        'version', 'status', 'compatibility', 'requirements', 'license_type',
        'downloads', 'rating_avg', 'rating_count', 'published_at'
    ];

    protected $casts = [
        'requirements' => 'array',
        'published_at' => 'datetime',
        'rating_avg' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function incrementDownload()
    {
        $this->increment('downloads');
    }
    
    public function recalculateRating()
    {
        $count = $this->reviews()->count();
        $avg = $count > 0 ? $this->reviews()->avg('rating') : 0;
        
        $this->update([
            'rating_avg' => round((float) $avg, 2),
            'rating_count' => $count
        ]);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'active')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    protected static function booted(): void
    {
        // Auto-generate slug from name when creating
        static::creating(function ($plugin) {
            if (empty($plugin->slug)) {
                $plugin->slug = static::generateUniqueSlug($plugin->name);
            }
        });

        // Update slug when name changes
        static::updating(function ($plugin) {
            if ($plugin->isDirty('name') && empty($plugin->slug)) {
                $plugin->slug = static::generateUniqueSlug($plugin->name);
            }
        });

        // When a plugin is soft deleted, also delete all associated favorites
        static::deleted(function ($plugin) {
            $plugin->favorites()->delete();
        });
    }

    /**
     * Generate a unique slug from the given name
     */
    protected static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        // Check for slug collisions and append numeric suffix if needed
        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}