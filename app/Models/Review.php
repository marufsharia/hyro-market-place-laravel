<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'plugin_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::created(fn($review) => $review->plugin->recalculateRating());
        static::updated(fn($review) => $review->plugin->recalculateRating());
        static::deleted(fn($review) => $review->plugin->recalculateRating());
    }
}
