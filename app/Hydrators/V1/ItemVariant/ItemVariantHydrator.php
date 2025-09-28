<?php

namespace App\Hydrators\V1\ItemVariant;

use App\DTO\V1\ItemVariant\CreateItemVariantDTO;
use App\DTO\V1\ItemVariant\UpdateItemVariantDTO;
use App\Http\Requests\V1\ItemVariant\CreateItemVariantRequest;
use App\Http\Requests\V1\ItemVariant\UpdateItemVariantRequest;
use App\Hydrators\V1\BaseHydrator;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Services\V1\Item\SkuGeneratorService;

class ItemVariantHydrator extends BaseHydrator
{
    public function __construct(
        private readonly SkuGeneratorService $service
    ) {}

    public function fromCreateRequest(CreateItemVariantRequest $request, Item $item): CreateItemVariantDTO
    {
        $data = $request->validated();
        $name = (string) $data['name'];
        $sku = $this->service->generateSku($name, $item->store_id, true, $name, $item->id);

        return new CreateItemVariantDTO(
            name: $name,
            description: array_key_exists('description', $data) ? (string) $data['description'] : null,
            price_cents: array_key_exists('price_cents', $data) ? (int) $data['price_cents'] : null,
            sku: $sku,
            is_active: $data['is_active'] ?? true,
            store_id: $item->store_id
        );
    }

    public function fromUpdateRequest(UpdateItemVariantRequest $request, Item $item, ItemVariant $itemVariant): UpdateItemVariantDTO
    {
        $data = $request->validated();

        return new UpdateItemVariantDTO(
            id: $itemVariant->id,
            item_id: $item->id,
            store_id: $item->store_id,
            name: array_key_exists('name', $data) ? (string) $data['name'] : null,
            description: array_key_exists('description', $data) ? (string) $data['description'] : null,
            price_cents: array_key_exists('price_cents', $data) ? (int) $data['price_cents'] : null,
            is_active: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null
        );
    }
}
