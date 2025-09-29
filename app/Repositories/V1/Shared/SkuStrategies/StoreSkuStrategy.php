<?php

namespace App\Repositories\V1\Shared\SkuStrategies;

use App\Models\V1\Store;

class StoreSkuStrategy extends BaseSkuStrategy
{

    public function buildBase(string $name, array $context, array $config): string
    {
        $baseSku = $this->normalizeString($name, $config['length'], $config);

        return $this->addPrefix($baseSku, $context, $config);
    }

    public function checkExists(string $sku, ?string $scope, array $context): bool
    {
        $query = Store::where('sku', $sku);

        if ($scope) {
            $query->where('scope', $scope);
        }

        return $query->exists();
    }
}
