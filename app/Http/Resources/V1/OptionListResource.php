<?php

namespace App\Http\Resources\V1;

use App\Models\V1\OptionList;
use Illuminate\Http\Request;

/**
 * @mixin OptionList
 * @property $pivot
 */
class OptionListResource extends AbstractPivotResource
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
                'item'            => $this->getPivotValue('item_id'),
                'is_required'     => $this->getPivotValue('is_required'),
                'min_selections'  => $this->getPivotValue('min_selections'),
                'max_selections'  => $this->getPivotValue('max_selections'),
                'display_order'   => $this->getPivotValue('display_order'),
                'is_active'       => $this->getPivotValue('is_active'),
            ],

            'options' => OptionResource::collection($this->whenLoaded('options')),
            'options_count' => $this->whenLoaded('options', fn() => $this->options->count()),
            'created_at'     => $this->created_at->toISOString(),
            'updated_at'     => $this->updated_at->toISOString(),
        ];
    }
}
