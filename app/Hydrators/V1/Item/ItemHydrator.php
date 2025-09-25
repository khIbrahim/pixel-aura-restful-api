<?php

namespace App\Hydrators\V1\Item;

use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Item\CreateVariantDTO;
use App\DTO\V1\Option\CreateOptionDTO;
use App\Http\Requests\V1\StoreMember\StoreItemRequest;
use App\Services\V1\Item\SkuGeneratorService;

readonly class ItemHydrator
{
    public function __construct(private SkuGeneratorService $skuGeneratorService) {}

    public function fromRequest(StoreItemRequest $request): CreateItemDTO
    {
        $data = $request->validated();

        $storeId    = $request->attributes->get('store')->id ?? $request->user()->store_id;
        $categoryId = $data['category_id'];
        $name       = $data['name'];
        $sku        = $data['sku'] ?? $this->skuGeneratorService->generateItemSku($name, $storeId);

        $options = [];
        foreach ((array) ($data['options'] ?? []) as $option) {
            $options[] = new CreateOptionDTO(
                store_id: $storeId,
                id: $option['id'] ?? null,
                name: $option['name'] ?? null,
                description: $option['description'] ?? null,
                price_cents: $option['price_cents'] ?? null,
            );
        }

        $variants = [];
        foreach ((array) ($data['variants'] ?? []) as $variant) {
            $variants[] = new CreateVariantDTO(
                id: $variant['id'] ?? null,
                name: $variant['name'] ?? null,
                description: $variant['description'] ?? null,
                price_cents: $variant['price_cents'] ?? null,
                sku: $variant['sku'] ?? $this->skuGeneratorService->generateVariantSku($name, $variant['name'] ?? '', $storeId, null),
                is_active: $variant['is_active'] ?? true,
            );
        }

        $ingredients = [];
        foreach ((array) ($data['ingredients'] ?? []) as $ingredient) {
            $ingredients[] = new CreateIngredientDTO(
                id: $ingredient['id'] ?? null,
                store_id: $storeId,
                name: $ingredient['name'] ?? null,
                description: $ingredient['description'] ?? null,
                is_allergen: $ingredient['is_allergen'] ?? false,
                is_mandatory: $ingredient['is_mandatory'] ?? true,
                is_active: $ingredient['is_active'] ?? true,
                cost_per_unit_cents: $ingredient['cost_per_unit_cents'] ?? 0,
            );
        }

        $data  = $request->validated();
        $image = $data['image'] ?? $data['image_url'] ?? null;
        $type  = $data['type'] ?? null;

        return new CreateItemDTO(
            name: $name,
            store_id: $storeId,
            category_id: $categoryId,
            base_price_cents: $data['base_price_cents'],
            current_cost_cents: $data['current_cost_cents'] ?? 0,
            currency: $data['currency'] ?? config('pos.currency'),
            sku: $sku,
            barcode: $data['barcode'] ?? null,
            description: $data['description'] ?? null,
            is_active: (bool) ($data['is_active'] ?? true),
            track_inventory: (bool) ($data['track_inventory'] ?? false),
            stock: (int) ($data['stock'] ?? 0),
            loyalty_eligible: (bool) ($data['loyalty_eligible'] ?? false),
            age_restriction: (int) ($data['age_restriction'] ?? 0),
            reorder_level: $data['reorder_level'] ?? null,
            weight_grams: $data['weight_grams'] ?? null,
            tags: (array) ($data['tags'] ?? []),
            metadata: (array) ($data['metadata'] ?? []),
            options: $options,
            variants: $variants,
            ingredients: $ingredients,
            tax_id: $data['tax_id'] ?? null,
            created_by: $request->attributes->get('store_member')->id ?? $request->user()->id,
            image: $image,
        );
    }
}
