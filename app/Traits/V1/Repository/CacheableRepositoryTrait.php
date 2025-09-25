<?php

namespace App\Traits\V1\Repository;

use Closure;
use Illuminate\Support\Facades\Cache;

trait CacheableRepositoryTrait
{

    protected int $cacheTtl = 600;

    protected function remember(string $key, Closure $callback, array $tags = [], ?int $ttl = null)
    {
        $ttl = $ttl ?? $this->cacheTtl + mt_rand(0, 60);

        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    protected function invalidate(int $storeId, array $extraKeys = []): void
    {
        Cache::tags(['store:' . $storeId])->flush();

        foreach ($extraKeys as $key) {
            Cache::forget($key);
        }
    }

    abstract protected function getTag(): string;

}
