<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractPivotResource extends JsonResource
{
    /**
     * @param string $attribute
     * @return mixed
     */
    protected function getPivotValue(string $attribute): mixed
    {
        if (isset($this->pivot) && array_key_exists($attribute, $this->pivot->getAttributes())) {
            return $this->pivot->$attribute;
        }

        return $this->$attribute;
    }

    protected function hasPivot(): bool
    {
        return isset($this->pivot) && $this->pivot->exists;
    }
}
