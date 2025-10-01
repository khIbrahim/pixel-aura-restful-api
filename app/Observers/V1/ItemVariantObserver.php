<?php

namespace App\Observers\V1;

use App\Models\V1\ItemVariant;
use App\Services\V1\Catalog\CatalogVersionManager;

class ItemVariantObserver
{

    private const array CATALOG_AFFECTING_FIELDS = [
        'name',
        'description',
        'is_active',
        'price_cents',
        'sku'
    ];

    public function __construct(
        private readonly CatalogVersionManager $versionManager
    ){}

    public function created(ItemVariant $itemVariant): void
    {
        $this->versionManager->incrementVersion($itemVariant->store, "Item variant créée", [
            'item_variant_id'   => $itemVariant->id,
            'item_variant_name' => $itemVariant->name,
            'item_variant_sku'  => $itemVariant->sku,
        ]);
    }

    public function updated(ItemVariant $itemVariant): void
    {
        $changedFields  = $itemVariant->getChanges();
        $affectedFields = array_intersect(self::CATALOG_AFFECTING_FIELDS, $changedFields);

        if(empty($affectedFields)){
            return;
        }

        $this->versionManager->incrementVersion($itemVariant->store, "Item variant mis à jour", [
            'item_variant_id'         => $itemVariant->id,
            'item_variant_name'       => $itemVariant->name,
            'item_variant_sku'        => $itemVariant->sku,
            'changed_fields'          => $affectedFields,
            'all_changes'             => $changedFields,
        ]);
    }

    public function deleted(ItemVariant $itemVariant): void
    {
        $this->versionManager->incrementVersion($itemVariant->store, "Item variant supprimé", [
            'item_variant_id'   => $itemVariant->id,
            'item_variant_name' => $itemVariant->name,
            'item_variant_sku'  => $itemVariant->sku,
        ]);
    }

}
