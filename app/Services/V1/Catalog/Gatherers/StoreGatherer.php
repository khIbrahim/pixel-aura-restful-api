<?php

namespace App\Services\V1\Catalog\Gatherers;

use App\Models\V1\Store;
use Illuminate\Support\Collection;

class StoreGatherer implements GathererInterface
{

    public function gather(Store $store): Collection
    {
        return collect(['store' => $store]);
    }
}
