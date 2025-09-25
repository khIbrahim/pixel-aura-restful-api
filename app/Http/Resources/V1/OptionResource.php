<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Option;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Option
 */
class OptionResource extends AbstractPivotResource
{

    public function toArray($request): array
    {
        return [
            'id'          => $this->isPivot && $this->pivot->id ? $this->pivot->id : $this->id,
            'store_id'    => $this->isPivot && $this->pivot->store_id ? $this->pivot->store_id : $this->store_id,
            'name'        => $this->isPivot && $this->pivot->name ? $this->pivot->name : $this->name,
            'description' => $this->isPivot && $this->pivot->description ? $this->pivot->description : $this->description,
            'price_cents' => $this->isPivot && $this->pivot->price_cents ? $this->pivot->price_cents : $this->price_cents,
            'is_active'   => $this->isPivot && $this->pivot->is_active ? $this->pivot->is_active : $this->is_active,
            'created_at'  => $this->isPivot && $this->pivot->created_at ? $this->pivot->created_at : $this->created_at,
            'updated_at'  => $this->isPivot && $this->pivot->updated_at ? $this->pivot->updated_at : $this->updated_at,
        ];
    }

}
