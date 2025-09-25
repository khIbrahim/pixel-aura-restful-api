<?php

namespace App\Http\Resources\V1;

use App\Models\V1\OptionList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var OptionList $this */
        return [
            'id'             => $this->id,
            'store_id'       => $this->store_id,
            'name'           => $this->name,
            'description'    => $this->description,
            'sku'            => $this->sku,
            'min_selections' => $this->min_selections,
            'max_selections' => $this->max_selections,
            'is_active'      => $this->is_active,
            'created_at'     => $this->created_at->toISOString(),
            'updated_at'     => $this->updated_at->toISOString(),

            'options'        => OptionResource::collection($this->whenLoaded('options')),
            'active_options' => OptionResource::collection($this->whenLoaded('activeOptions')),
            'items'          => ItemResource::collection($this->whenLoaded('items')),

            'options_count'        => $this->whenLoaded('options', fn() => $this->options->count()),
            'active_options_count' => $this->whenLoaded('activeOptions', fn() => $this->activeOptions->count()),
        ];
    }
}
