<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class AbstractPivotResource extends JsonResource
{

    public function __construct($resource, protected bool $isPivot = false)
    {
        parent::__construct($resource);
    }

    public static function collection($resource, bool $isPivot = false)
    {
        return parent::collection($resource)->map(function ($item) use ($isPivot) {
            $item->isPivot = $isPivot;
            return $item;
        });
    }

}
