<?php

namespace App\Contracts\V1\Store;

use App\DTO\V1\Store\CreateStoreDTO;
use App\DTO\V1\Store\UpdateStoreDTO;
use App\Models\V1\Store;

interface StoreServiceInterface
{

    public function create(CreateStoreDTO $data): Store;
    public function update(UpdateStoreDTO $data, Store $store): Store;

}
