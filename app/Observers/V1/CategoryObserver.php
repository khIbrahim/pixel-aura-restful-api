<?php

namespace App\Observers\V1;

use App\Models\V1\Category;
use App\Services\V1\Catalog\CatalogVersionManager;

class CategoryObserver
{

    private const array CATALOG_AFFECTED_FIELDS = [
        'name',
        'description',
        'is_active',
        'position',
        'parent_id',
        'sku'
    ];

    public function __construct(
        private readonly CatalogVersionManager $versionManager
    ){}

    public function created(Category $category): void
    {
        $this->versionManager->incrementVersion($category->store, "Catégorie créée", [
            'category_id'   => $category->id,
            'category_name' => $category->name,
            'category_sku'  => $category->sku,
        ]);
    }

    public function updated(Category $category): void
    {
        $changedFields  = $category->getChanges();
        $affectedFields = array_intersect(self::CATALOG_AFFECTED_FIELDS, $changedFields);

        if(empty($affectedFields)) {
            return;
        }

        $this->versionManager->incrementVersion($category->store, "Catégorie mise à jour", [
            'category_id'      => $category->id,
            'category_name'    => $category->name,
            'category_sku'     => $category->sku,
            'changed_fields'   => $affectedFields,
            'all_changes'      => $changedFields,
        ]);
    }

    public function deleted(Category $category): void
    {
        $this->versionManager->incrementVersion($category->store, "Catégorie supprimée", [
            'category_id'   => $category->id,
            'category_name' => $category->name,
            'category_sku'  => $category->sku,
        ]);
    }

}
