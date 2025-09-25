<?php

namespace App\Hydrators\V1\OptionList;

use App\DTO\V1\OptionList\StoreOptionListDTO;
use App\Http\Requests\V1\OptionList\StoreOptionListRequest;
use App\Services\V1\Item\SkuGeneratorService;

readonly class OptionListHydrator
{

    public function __construct(
        private SkuGeneratorService $skuGeneratorService
    ){}

    public function fromRequest(StoreOptionListRequest $request): StoreOptionListDTO
    {
        $data    = $request->validated();
        $storeId = $request->attributes->get('store')?->id ?? $request->attributes->get('device')?->id ?? $request->user()->id;
        $name    = (string) $data['name'];
        $sku     = $this->skuGeneratorService->generateSku($name, $storeId);

        return new StoreOptionListDTO(
            name: (string) $data['name'],
            storeId: $storeId,
            description: array_key_exists('description', $data) ? (string) $data['description'] : null,
            sku: $sku,
            min_selections: array_key_exists('min_selections', $data) ? (int) $data['min_selections'] : 0,
            max_selections: array_key_exists('max_selections', $data) ? (int) $data['max_selections'] : null,
            is_active: !array_key_exists('is_active', $data) || $data['is_active'],
        );
    }

}
