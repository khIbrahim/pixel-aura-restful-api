<?php

namespace App\Contracts\V1\Store;

use App\DTO\V1\Store\CreateStoreDTO;
use App\Models\V1\Store;

interface StoreServiceInterface
{

    public function create(CreateStoreDTO $data): Store;

}
