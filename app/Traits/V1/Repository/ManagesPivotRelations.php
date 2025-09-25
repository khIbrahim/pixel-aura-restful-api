<?php

namespace App\Traits\V1\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ManagesPivotRelations
{

    public function syncPivotData(Model $model, string $relation, array $data, array $pivotColumns = []): Collection
    {
        $syncData = [];

        foreach ($data as $id => $pivotData){
            $syncData[$id] = array_filter($pivotData, function ($key) use ($pivotColumns) {
                return in_array($key, $pivotColumns);
            }, ARRAY_FILTER_USE_KEY);

            if(! isset($syncData[$id]['created_at'])){
                $syncData[$id]['created_at'] = now();
            }

            $syncData[$id]['updated_at'] = now();
        }

        $model->$relation()->syncWithoutDetaching($syncData);

        $model->unsetRelation($relation);

        return $model->$relation()->withPivot($pivotColumns)->get();
    }

}
