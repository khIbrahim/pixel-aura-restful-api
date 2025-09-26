<?php

namespace App\Repositories\V1\Store;

use App\Contracts\V1\Store\StoreRepositoryInterface;
use App\Models\V1\Store;
use App\Repositories\V1\BaseRepository;

class StoreRepository extends BaseRepository implements StoreRepositoryInterface
{

    public function model(): string
    {
        return Store::class;
    }
}
