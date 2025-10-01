<?php

namespace App\Services\V1\Catalog;

use App\Contracts\V1\Store\StoreRepositoryInterface;
use App\Models\V1\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class CatalogVersionManager
{

    public function __construct(
        private CatalogCacheService      $cacheService,
        private StoreRepositoryInterface $storeRepository
    ){}

    public function incrementVersion(Store $store, string $reason, array $context = []): void
    {
        DB::transaction(function () use ($context, $reason, $store) {
            $oldVersion = $store->menu_version;
            $newVersion = $this->storeRepository->increment($store, 'menu_version');

            $this->cacheService->invalidateStore($store);

            Log::info("Menu du store incrémenté", [
                'store_id'     => $store->id,
                'old_version'  => $oldVersion,
                'new_version'  => $newVersion,
                'reason'       => $reason,
                'context'      => $context,
            ]);
        });
    }

}
