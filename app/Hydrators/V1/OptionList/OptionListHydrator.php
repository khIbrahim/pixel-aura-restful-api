<?php

namespace App\Hydrators\V1\OptionList;

use App\Contracts\V1\OptionList\OptionListRepositoryInterface;
use App\DTO\V1\OptionList\CreateOptionListDTO;
use App\DTO\V1\OptionList\OptionListPivotDTO;
use App\DTO\V1\OptionList\UpdateOptionListDTO;
use App\Http\Requests\V1\ItemAttachment\AttachOptionListsRequest;
use App\Http\Requests\V1\OptionList\CreateOptionListRequest;
use App\Http\Requests\V1\OptionList\UpdateOptionListRequest;
use App\Hydrators\V1\BaseHydrator;
use App\Models\V1\OptionList;
use App\Services\V1\Item\SkuGeneratorService;

class OptionListHydrator extends BaseHydrator
{

    public function __construct(
        private readonly OptionListRepositoryInterface $optionListRepository,
        private readonly SkuGeneratorService $skuGeneratorService
    ){}

    public function fromCreateRequest(CreateOptionListRequest $request): CreateOptionListDTO
    {
        $data    = $request->validated();
        $storeId = $request->attributes->get('store')?->id ?? $request->attributes->get('device')?->id ?? $request->user()->id;
        $name    = (string) $data['name'];
        $sku     = $this->skuGeneratorService->generateSku($name, $storeId);

        return $this->fromArray(array_merge($data, [
            'store_id' => $storeId,
            'sku'      => $sku,
        ]), CreateOptionListDTO::class);
    }

    public function fromUpdateRequest(UpdateOptionListRequest $request): UpdateOptionListDTO
    {
        return $this->fromRequest($request, UpdateOptionListDTO::class);
    }

    public function fromAttachRequest(AttachOptionListsRequest $request, bool $toArray = true): array
    {
        $data        = $request->validated();
        $optionLists = (array) $data['option_lists'] ?? [];

        $optionsPivot = [];
        foreach ($optionLists as $optionListData) {
            /** @var OptionList $optionList */
            $optionList = $this->optionListRepository->findOrFail((int) $optionListData['id']);

            $optionsPivot[$optionList->id] = new OptionListPivotDTO(
                option_list_id: $optionList->id,
                store_id: $optionList->store_id,
                is_required: $optionListData['is_required'] ?? false,
                min_selections: $optionListData['min_selections'] ?? $optionList->min_selections,
                max_selections: $optionListData['max_selections'] ?? $optionList->max_selections,
                display_order: $optionListData['display_order'] ?? 0,
                is_active: $optionListData['is_active'] ?? $optionList->is_active,
            );
        }

        return $toArray ? array_map(fn($o) => $o->toArray(), $optionsPivot) : $optionsPivot;
    }
}
