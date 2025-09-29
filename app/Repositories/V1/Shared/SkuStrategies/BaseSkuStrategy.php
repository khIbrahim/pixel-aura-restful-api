<?php

namespace App\Repositories\V1\Shared\SkuStrategies;

use Illuminate\Support\Str;

abstract class BaseSkuStrategy
{

    abstract public function buildBase(string $name, array $context, array $config): string;

    abstract public function checkExists(string $sku, ?string $scope, array $context): bool;

    public function buildVariant(string $parentName, string $variantName, array $context, array $config): string
    {
        $basePart    = $this->normalizeString($parentName, $config['length'] - $config['variant_length'] - 1, $config);
        $variantPart = $this->normalizeString($variantName, $config['variant_length'], $config);

        return $basePart . $config['separator'] . $variantPart;
    }

    public function buildWithStore(string $name, int $storeId, array $context, array $config): string
    {
        $storePrefix = 'S' . str_pad($storeId, 3, '0', STR_PAD_LEFT);
        $maxBase     = $config['length'] - strlen($storePrefix) - 1;
        $baseSku     = $this->normalizeString($name, $maxBase, $config);
        return $storePrefix . $config['separator'] . $baseSku;
    }

    protected function normalizeString(string $input, int $maxLength, array $config): string
    {
        $normalized = Str::of($input)
            ->ascii()
            ->replaceMatches('/[^A-Z0-9\s\-_]+/i', '')
            ->replaceMatches('/[\s\-_]+/', $config['separator'])
            ->trim($config['separator'])
            ->limit($maxLength);

        return match ($config['case']) {
            'upper' => $normalized->upper(),
            'lower' => $normalized->lower(),
            default => $normalized->toString(),
        };
    }

    protected function addPrefix(string $sku, array $context, array $config): string
    {
        if (! $config['prefix_enabled'] || ! isset($context['prefix'])) {
            return $sku;
        }

        $prefix = $this->normalizeString($context['prefix'], 4, $config);
        return $prefix . $config['separator'] . $sku;
    }

}
