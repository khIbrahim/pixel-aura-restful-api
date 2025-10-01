<?php

namespace App\Observers\V1;

use App\Models\V1\Item;
use App\Services\V1\Catalog\CatalogVersionManager;

class ItemObserver
{

    private const array CATALOG_AFFECTING_FIELDS = [
        'name',
        'description',
        'is_active',
        'base_price_cents',
        'category_id',
        'sku',
        'currency',
        'timezone',
    ];

    public function __construct(
        private readonly CatalogVersionManager $versionManager
    ){}

    public function created(Item $item): void
    {
        $this->versionManager->incrementVersion($item->store, "Item créé", [
            'item_id'   => $item->id,
            'item_name' => $item->name,
            'item_sku'  => $item->sku,
        ]);
    }

    public function updated(Item $item): void
    {
        $changedFields  = $item->getChanges();
        $affectedFields = array_intersect(self::CATALOG_AFFECTING_FIELDS, $changedFields);

        if(empty($affectedFields)){
            return;
        }

        $this->versionManager->incrementVersion($item->store, "Item mis à jour", [
            'item_id'         => $item->id,
            'item_name'       => $item->name,
            'item_sku'        => $item->sku,
            'changed_fields'  => $affectedFields,
            'all_changes'     => $changedFields,
        ]);
    }

    public function deleted(Item $item): void
    {
        $this->versionManager->incrementVersion($item->store, "Item supprimé", [
            'item_id'   => $item->id,
            'item_name' => $item->name,
            'item_sku'  => $item->sku,
        ]);
    }

}
