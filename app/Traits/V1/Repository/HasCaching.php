<?php

namespace App\Traits\V1\Repository;

use App\Repositories\V1\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @mixin BaseRepository
 */
trait HasCaching
{
    protected int $cacheTime = 3600;

    protected function getCacheKey(string $suffix = ''): string
    {
        $modelName = class_basename($this->model);
        return strtolower($modelName) . ($suffix ? ".{$suffix}" : '');
    }

    public function findCached(int $id, array $columns = ['*'], ?int $ttl = null): ?Model
    {
        $key = $this->getCacheKey("find.{$id}." . md5(serialize($columns)));

        return Cache::tags($this->cacheTags)->remember(
            $key,
            $ttl ?? $this->cacheTime,
            fn() => $this->find($id, $columns)
        );
    }

    public function allCached(array $columns = ['*'], ?int $ttl = null): Collection
    {
        $key = $this->getCacheKey("all." . md5(serialize($columns)));

        return Cache::tags($this->cacheTags)->remember(
            $key,
            $ttl ?? $this->cacheTime,
            fn() => $this->all($columns)
        );
    }

    public function cacheQuery(Builder $query, string $suffix, ?int $ttl = null): Collection
    {
        $key = $this->getCacheKey($suffix);

        return Cache::tags($this->cacheTags)->remember(
            $key,
            $ttl ?? $this->cacheTime,
            fn() => $query->get()
        );
    }

    public function forgetCache(string $suffix = ''): bool
    {
        return Cache::forget($this->getCacheKey($suffix));
    }

    public function flushCache(): bool
    {
        return Cache::tags($this->cacheTags)->flush();
    }

    public function setCacheTags(array $tags): static
    {
        $this->cacheTags = $tags;
        return $this;
    }

    public function setCacheTime(int $seconds): static
    {
        $this->cacheTime = $seconds;
        return $this;
    }
}
