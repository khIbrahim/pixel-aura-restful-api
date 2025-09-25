<?php

namespace App\Services\V1\Item;

use App\Exceptions\V1\Item\FailedToGenerateUniqueSkuException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SkuGeneratorService
{

    public function generateSku(string $name, int $storeId, bool $isVariant = false, ?string $variantName = null, ?int $parentItemId = null): string
    {
        $baseSku = $this->buildBaseSku($name, $variantName, $isVariant);

        return $this->ensureUniqueSku($baseSku, $storeId, $isVariant, $parentItemId);
    }

    public function generateItemSku(string $name, int $storeId): string
    {
        return $this->generateSku($name, $storeId);
    }

    public function generateVariantSku(string $itemName, string $variantName, int $storeId, ?int $itemId): string
    {
        return $this->generateSku($itemName, $storeId, true, $variantName, $itemId);
    }

    private function buildBaseSku(string $name, ?string $variantName, bool $isVariant): string
    {
        $basePart = $this->normalizeNameForSku($name, config('pos.item.sku.base_length', 20));

        if(! $isVariant || ! $variantName) {
            return $basePart;
        }

        $variantPart = $this->normalizeNameForSku($variantName, config('pos.item.sku.variant_length', 20));
        $maxBaseWithVariant = config('pos.item.sku.base_length', 24) - strlen($variantPart) - 1;
        if (strlen($basePart) > $maxBaseWithVariant) {
            $basePart = substr($basePart, 0, $maxBaseWithVariant);
        }

        return $basePart . config('pos.item.sku.separator', '-') . $variantPart;
    }

    /**
     * @throws FailedToGenerateUniqueSkuException
     */
    private function ensureUniqueSku(string $baseSku, int $storeId, bool $isVariant, ?int $parentItemId): string
    {
        $sku         = $baseSku;
        $attempt     = 1;
        $maxAttempts = config('pos.item.sku.max_attempts', 10);

        while ($this->skuExists($sku, $storeId, $isVariant, $parentItemId) && $attempt <= $maxAttempts) {
            $sku = $baseSku . config('pos.item.sku.separator') . $attempt;
            $attempt++;
        }

        if ($attempt > $maxAttempts) {
            throw new FailedToGenerateUniqueSkuException("échec de la génération d'un SKU unique après $maxAttempts tentatives.");
        }

        return $sku;
    }

    private function normalizeNameForSku(string $name, int $maxLength): string
    {
        return Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9\s]+/', '')
            ->replaceMatches('/\s+/', config('pos.item.sku.separator', '-'))
            ->replaceMatches('/-+/', config('pos.item.sku.separator', '-'))
            ->trim(config('pos.item.sku.separator', '-'))
            ->limit($maxLength)
            ->toString();
    }

    private function skuExists(string $sku, int $storeId, bool $isVariant, ?int $parentItemId): bool
    {
        if ($isVariant) {
            return DB::table('item_variants')
                ->when($parentItemId, fn($q) => $q->where('item_id', $parentItemId))
                ->where('sku', $sku)
                ->exists();
        }

        return DB::table('items')
            ->where('store_id', $storeId)
            ->where('sku', $sku)
            ->exists();
    }

}
