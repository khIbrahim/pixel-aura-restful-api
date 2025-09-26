<?php

namespace App\Http\Resources\V1;

use App\Models\V1\OptionList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OptionList
 * @property $pivot
 */
class OptionListResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'sku'            => $this->sku,
            'store_id'       => $this->store_id,
            'is_active'      => $this->is_active,
            'min_selections' => $this->min_selections,
            'max_selections' => $this->max_selections,

            'pivot' => [
                'item'            => $this->pivot->item_id,
                'is_required'     => $this->pivot->is_required,
                'min_selections'  => $this->pivot->min_selections,
                'max_selections'  => $this->pivot->max_selections,
                'display_order'   => $this->pivot->display_order,
                'is_active'       => $this->pivot->is_active,
                'created_at'      => $this->pivot->created_at?->toISOString(),
                'updated_at'      => $this->pivot->updated_at?->toISOString(),
            ],

            'options' => OptionResource::collection($this->whenLoaded('options')),
            'options_count' => $this->whenLoaded('options', fn() => $this->options->count()),
            'created_at'     => $this->created_at->toISOString(),
            'updated_at'     => $this->updated_at->toISOString(),
        ];
    }
}
