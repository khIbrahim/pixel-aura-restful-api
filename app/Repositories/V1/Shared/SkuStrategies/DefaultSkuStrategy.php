<?php

namespace App\Repositories\V1\Shared\SkuStrategies;

class DefaultSkuStrategy extends BaseSkuStrategy
{

    public function buildBase(string $name, array $context, array $config): string
    {
        if(isset($context['store_id'])) {
            $baseSku = $this->buildWithStore($name, $context['store_id'], $context, $config);
        } else {
            $baseSku = $this->normalizeString($name, $config['length'], $config);
        }

        return $this->addPrefix($baseSku, $context, $config);
    }

    public function checkExists(string $sku, ?string $scope, array $context): bool
    {
        return false;
    }
}
