<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Ingredient;
use Illuminate\Http\Request;

/**
 * @mixin Ingredient
 */
class IngredientResource extends AbstractPivotResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->getPivotValue('id'),
            'store_id'            => $this->getPivotValue('store_id'),
            'name'                => $this->getPivotValue('name'),
            'description'         => $this->getPivotValue('description'),
            'is_allergen'         => $this->getPivotValue('is_allergen'),
            'is_active'           => $this->getPivotValue('is_active'),
            'unit'                => $this->getPivotValue('unit'),
            'cost_per_unit_cents' => $this->getPivotValue('cost_per_unit_cents'),
            'created_at'          => $this->getPivotValue('created_at'),
            'updated_at'          => $this->getPivotValue('updated_at'),
            'images' => [
                'main' => $this->getMainImageUrls(),
            ]
        ];
    }

    private function getMainImageUrls(): ?array
    {
        $media = $this->getFirstMedia('main_image');

        if (! $media) {
            return null;
        }

        return [
            'id'        => $media->id,
            'original'  => $media->getUrl(),
            'thumbnail' => $media->getUrl('thumbnail'),
            'banner'    => $media->getUrl('banner'),
            'icon'      => $media->getUrl('icon'),
        ];
    }

}
