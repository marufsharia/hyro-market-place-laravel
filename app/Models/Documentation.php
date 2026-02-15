<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Documentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'version',
        'order',
        'is_published',
        'tags',
        'views'
    ];

    protected $casts = [
        'tags' => 'array',
        'is_published' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(DocumentationCategory::class, 'category_id');
    }

    public function incrementViews()
    {
        $this->increment('views');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeVersion($query, $version)
    {
        return $query->where('version', $version);
    }

    protected static function booted(): void
    {
        static::creating(function ($doc) {
            if (empty($doc->slug)) {
                $doc->slug = static::generateUniqueSlug($doc->title);
            }
        });
    }

    protected static function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
