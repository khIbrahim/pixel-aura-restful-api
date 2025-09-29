<?php

namespace App\Repositories\V1\Shared;

use App\Contracts\V1\Shared\SkuGeneratorServiceInterface;
use App\Exceptions\V1\Item\FailedToGenerateUniqueSkuException;
use App\Models\V1\Category;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Models\V1\Store;
use App\Repositories\V1\Shared\SkuStrategies\BaseSkuStrategy;
use App\Repositories\V1\Shared\SkuStrategies\CategorySkuStrategy;
use App\Repositories\V1\Shared\SkuStrategies\DefaultSkuStrategy;
use App\Repositories\V1\Shared\SkuStrategies\ItemSkuStrategy;
use App\Repositories\V1\Shared\SkuStrategies\ItemVariantSkuStrategy;
use App\Repositories\V1\Shared\SkuStrategies\StoreSkuStrategy;
use Illuminate\Support\Facades\Cache;

class SkuGeneratorService implements SkuGeneratorServiceInterface
{

    private const int CACHE_TTL    = 300; // 5 minutes
    private const int MAX_ATTEMPTS = 20;

    public function __construct(
        protected array $config = []
    ){
        $this->config = array_merge([
            'length'         => 24,
            'variant_length' => 8,
            'separator'      => '-',
            'prefix_enabled' => true,
            'suffix_enabled' => true,
            'case'           => 'upper',
            'allowed_chars'  => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        ], config('sku_generator', [/** TODO */]), $this->config);
    }

    public function generate(string $name, string $modelClass, ?string $scope = null, ?array $context = []): string
    {
        $strategy = $this->getStrategy($modelClass);
        $baseSku  = $strategy->buildBase($name, $context, $this->config);

        return $this->ensureUnique($baseSku, $modelClass, $scope, $context);
    }

    public function generateForVariant(string $parentName, string $variantName, string $modelClass, ?string $scope = null, ?array $context = []): string {
        $strategy = $this->getStrategy($modelClass);
        $baseSku = $strategy->buildVariant($parentName, $variantName, $context, $this->config);

        return $this->ensureUnique($baseSku, $modelClass, $scope, $context);
    }

    private function getStrategy(string $modelClass): BaseSkuStrategy
    {
        return match ($modelClass) {
            Item::class        => new ItemSkuStrategy(),
            ItemVariant::class => new ItemVariantSkuStrategy(),
            Category::class    => new CategorySkuStrategy(),
            Store::class       => new StoreSkuStrategy(),
//            \App\Models\V1\Option::class => new OptionSkuStrategy(),
            default            => new DefaultSkuStrategy(),
        };
    }

    private function ensureUnique(string $baseSku, string $modelClass, ?string $scope, array $context): string {
        $cacheKey = $this->getCacheKey($baseSku, $modelClass, $scope);

        return Cache::lock("sku_generation_$cacheKey", 30)->block(30, function () use ($baseSku, $modelClass, $scope, $context) {
            $sku     = $baseSku;
            $attempt = 1;

            while ($this->exists($sku, $modelClass, $scope, $context) && $attempt <= self::MAX_ATTEMPTS) {
                $sku = $this->generateVariation($baseSku, $attempt);
                $attempt++;
            }

            if ($attempt > self::MAX_ATTEMPTS) {
                throw new FailedToGenerateUniqueSkuException(
                    "Erreur lors de la génération d'un SKU unique après " . self::MAX_ATTEMPTS . " tentatives."
                );
            }

            Cache::put($this->getCacheKey($sku, $modelClass, $scope), true, self::CACHE_TTL);

            return $sku;
        });
    }

    private function exists(string $sku, string $modelClass, ?string $scope, array $context): bool
    {
        $cacheKey = $this->getCacheKey($sku, $modelClass, $scope);

        if (Cache::has($cacheKey)) {
            return true;
        }

        $strategy = $this->getStrategy($modelClass);
        $exists   = $strategy->checkExists($sku, $scope, $context);

        if ($exists) {
            Cache::put($cacheKey, true, self::CACHE_TTL);
        }

        return $exists;
    }

    private function generateVariation(string $baseSku, int $attempt): string
    {
        $maxLength   = $this->config['length'] - strlen((string)$attempt) - 1;
        $trimmedBase = substr($baseSku, 0, $maxLength);

        return $trimmedBase . $this->config['separator'] . $attempt;
    }

    private function getCacheKey(string $sku, string $modelClass, ?string $scope): string
    {
        return 'sku_exists:' . md5($sku . $modelClass . ($scope ?? ''));
    }

}
