<?php

namespace App\Observers\V1;

use App\Services\V1\Catalog\CatalogVersionManager;

class OptionObserver
{

    private const array CATALOG_AFFECTING_FIELDS = [
        'name',
        'description',
        'is_active',
        'price_cents',
        'option_list_id'
    ];

    public function __construct(
        private readonly CatalogVersionManager $versionManager
    ){}

    public function created($option): void
    {
        $this->versionManager->incrementVersion($option->store, "Option créée", [
            'option_id'   => $option->id,
            'option_name' => $option->name,
        ]);
    }

    public function updated($option): void
    {
        $changedFields  = $option->getChanges();
        $affectedFields = array_intersect(self::CATALOG_AFFECTING_FIELDS, $changedFields);

        if(empty($affectedFields)){
            return;
        }

        $this->versionManager->incrementVersion($option->store, "Option mise à jour", [
            'option_id'         => $option->id,
            'option_name'       => $option->name,
            'changed_fields'    => $affectedFields,
            'all_changes'       => $changedFields,
        ]);
    }

    public function deleted($option): void
    {
        $this->versionManager->incrementVersion($option->store, "Option supprimée", [
            'option_id'   => $option->id,
            'option_name' => $option->name,
        ]);
    }

}
