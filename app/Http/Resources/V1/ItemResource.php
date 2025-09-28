<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Item
 */
class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'store_id'          => $this->store_id,
            'category_id'       => $this->category_id,
            'tax_id'            => $this->tax_id,
            'name'              => $this->name,
            'sku'               => $this->sku,
            'barcode'           => $this->barcode,
            'description'       => $this->description,
            'currency'          => $this->currency,
            'base_price_cents'  => $this->base_price_cents,
            'current_cost_cents'=> $this->current_cost_cents,
            'is_active'         => $this->is_active,
            'track_inventory'   => $this->track_inventory,
            'stock'             => $this->stock,
            'loyalty_eligible'  => $this->loyalty_eligible,
            'age_restriction'   => $this->age_restriction,
            'reorder_level'     => $this->reorder_level,
            'weight_grams'      => $this->weight_grams,
            'tags'              => $this->tags,
            'metadata'          => $this->metadata,
            'created_by'        => $this->created_by,
            'updated_by'        => $this->updated_by,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'deleted_at'        => $this->deleted_at,

            'ingredients'       => IngredientResource::collection($this->whenLoaded('ingredients')),
            'options'           => OptionResource::collection($this->whenLoaded('options')),
            'variants'          => ItemVariantResource::collection($this->whenLoaded('variants')),
            'category'          => new CategoryResource($this->whenLoaded('category')),
            'store'             => new StoreResource($this->whenLoaded('store')),
            'creator'           => new StoreMemberResource($this->whenLoaded('creator')),
            'updater'           => new StoreMemberResource($this->whenLoaded('updater')),

            'images'            => $this->getImagesAttribute(),

            'variants_count'    => $this->when($this->relationLoaded('variants'), fn() => $this->variants->count()),
            'ingredients_count' => $this->when($this->relationLoaded('ingredients'), fn() => $this->ingredients->count()),
            'options_count'     => $this->when($this->relationLoaded('options'), fn() => $this->options->count()),
        ];
    }

    public function toCompactArray(): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'sku'              => $this->sku,
            'base_price_cents' => $this->base_price_cents,
            'currency'         => $this->currency,
            'is_active'        => $this->is_active,
            'stock'            => $this->stock,
            'track_inventory'  => $this->track_inventory,
            'thumbnail' => $this->formatCompactThumbnail(),
            'category' => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->sku,
            ]),
        ];
    }
}
