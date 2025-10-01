<?php

namespace App\Services\V1\Catalog\Gatherers;

use App\Models\V1\Store;
use Illuminate\Support\Collection;

interface GathererInterface
{

    public function gather(Store $store): Collection;

}
