<?php

namespace App\Services\V1\Catalog;

use App\Models\V1\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CatalogCacheService
{

    private const int CACHE_TTL       = 3600;
    private const string CACHE_PREFIX = 'catalog_';

    public function getCachedCatalog(Store $store, string $format, string $channel): ?array
    {
        $cacheKey = $this->buildCacheKey($store, $format, $channel);

        $cached = Cache::tags($this->getCacheTags($store))->get($cacheKey);

        if ($cached){
            Log::debug('Catalog cache hit', [
                'store_id'     => $store->id,
                'menu_version' => $store->menu_version,
                'format'       => $format,
                'channel'      => $channel,
                'cache_key'    => $cacheKey,
            ]);
        }

        return $cached;
    }

    public function cacheCatalog(array $catalog, Store $store, string $format, string $channel): void
    {
        $cacheKey = $this->buildCacheKey($store, $format, $channel);

        Cache::tags($this->getCacheTags($store))
            ->put($cacheKey, $catalog, self::CACHE_TTL);

        Log::info("Catalog cached", [
            'store_id'     => $store->id,
            'menu_version' => $store->menu_version,
            'format'       => $format,
            'channel'      => $channel,
            'cache_key'    => $cacheKey,
            'ttl_seconds'  => self::CACHE_TTL,
        ]);
    }

    public function invalidateStore(Store $store): void
    {
        $tags = $this->getCacheTags($store);

        Cache::tags($tags)->flush();

        Log::info("Cache catalog invalidated", [
            'store_id'     => $store->id,
            'menu_version' => $store->menu_version,
            'tags'         => $tags,
        ]);
    }

    private function buildCacheKey(Store $store, string $format, string $channel): string
    {
        return sprintf(
            "%s:%s!store:%d:v%d:%s",
            self::CACHE_PREFIX,
            $format,
            $store->id,
            $store->menu_version,
            $channel
        );
    }

    private function getCacheTags(Store $store): array
    {
        return [
            self::CACHE_PREFIX,
            "store:$store->id",
            "menu_version:$store->menu_version"
        ];
    }

}
