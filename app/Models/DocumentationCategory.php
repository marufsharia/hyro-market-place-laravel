<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentationCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'order'
    ];

    public function documentations()
    {
        return $this->hasMany(Documentation::class, 'category_id');
    }

    public function publishedDocumentations()
    {
        return $this->hasMany(Documentation::class, 'category_id')
            ->where('is_published', true)
            ->orderBy('order');
    }
}
