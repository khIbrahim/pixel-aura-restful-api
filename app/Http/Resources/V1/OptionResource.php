<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Option;
use Illuminate\Http\Request;

/**
 * @mixin Option
 */
class OptionResource extends AbstractPivotResource
{
    public function toArray(Request $request): array
    {
        return [
            'name'           => $this->getPivotValue('name'),
            'id'             => $this->getPivotValue('id'),
            'option_list_id' => $this->getPivotValue('option_list_id'),
            'store_id'       => $this->getPivotValue('store_id'),
            'description'    => $this->getPivotValue('description'),
            'price_cents'    => $this->getPivotValue('price_cents'),
            'is_active'      => $this->getPivotValue('is_active'),
            'created_at'     => $this->getPivotValue('created_at'),
            'updated_at'     => $this->getPivotValue('updated_at'),
        ];
    }
}
