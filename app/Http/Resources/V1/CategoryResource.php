<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->sku,
            'description' => $this->description,
            'tags'        => $this->tags,
            'position'    => $this->position,
            'parent_id'   => $this->parent_id,
            'parent'      => $this->whenLoaded('parent', fn() => $this->parent ? new CategoryResource($this->parent) : null),
            'children'    => CategoryResource::collection($this->whenLoaded('children')),
            'is_active'   => $this->is_active,
//            'image'       => $this->when($this->relationLoaded('media'), fn() => $this->formatImageObject()),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            'children_count' => $this->when($this->relationLoaded('children'), fn() => $this->children->count()),
            'items_count'    => $this->when($this->relationLoaded('items'), fn() => $this->items->count()),
            'items'          => ItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
