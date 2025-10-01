<?php

namespace App\Services\V1\Catalog\Formatters;

use Illuminate\Support\Collection;

interface CatalogFormatterInterface
{

    public function format(Collection $data): array;

}
