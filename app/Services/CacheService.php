<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    const PLUGIN_LIST_TTL = 300; // 5 minutes
    const PLUGIN_DETAIL_TTL = 600; // 10 minutes
    const CATEGORY_LIST_TTL = 3600; // 1 hour
    
    public function getPluginList(string $cacheKey, callable $callback): mixed
    {
        return Cache::remember($cacheKey, self::PLUGIN_LIST_TTL, $callback);
    }
    
    public function getPluginDetail(int $pluginId, callable $callback): mixed
    {
        return Cache::remember("plugin.{$pluginId}", self::PLUGIN_DETAIL_TTL, $callback);
    }
    
    public function invalidatePlugin(int $pluginId): void
    {
        Cache::forget("plugin.{$pluginId}");
        Cache::tags(['plugins'])->flush();
    }
    
    public function getCategoryList(callable $callback): mixed
    {
        return Cache::remember('categories.all', self::CATEGORY_LIST_TTL, $callback);
    }
    
    public function invalidateCategories(): void
    {
        Cache::forget('categories.all');
    }
}
