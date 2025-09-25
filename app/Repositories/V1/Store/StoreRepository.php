<?php

namespace App\Repositories\V1\Store;

use App\Models\V1\Store;
use App\Repositories\V1\BaseRepository;

class StoreRepository extends BaseRepository
{

    public function model(): string
    {
        return Store::class;
    }
}
