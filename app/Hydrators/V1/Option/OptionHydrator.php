<?php

namespace App\Hydrators\V1\Option;

use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\DTO\V1\Option\CreateOptionDTO;
use App\DTO\V1\Option\OptionPivotDTO;
use App\DTO\V1\Option\UpdateOptionDTO;
use App\Http\Requests\V1\ItemAttachment\AttachOptionsRequest;
use App\Http\Requests\V1\Option\CreateOptionRequest;
use App\Http\Requests\V1\Option\UpdateOptionRequest;
use App\Hydrators\V1\BaseHydrator;
use App\Models\V1\Option;

class OptionHydrator extends BaseHydrator
{

    public function __construct(
        private readonly OptionRepositoryInterface $optionRepository
    ){}

    public function fromCreateRequest(CreateOptionRequest $request): CreateOptionDTO
    {
        return $this->fromRequest($request, CreateOptionDTO::class);
    }

    public function fromUpdateRequest(UpdateOptionRequest $request): UpdateOptionDTO
    {
        return $this->fromRequest($request, UpdateOptionDTO::class);
    }

    public function fromAttachRequest(AttachOptionsRequest $request, bool $asArray = true): array
    {
        $data       = $request->validated();
        $optionsIds = array_column($data['options'], 'id');

        $existingOptions = $this->optionRepository->findOptionsByIds($optionsIds);

        $options = [];
        foreach ($data['options'] as $optionData) {
            $id     = (int) $optionData['id'];
            /** @var Option $option */
            $option = $existingOptions->get($id); // ça existe vu que j'ai fait la validation avec le form request

            $options[$id] = new OptionPivotDTO(
                option_id: $option->id,
                store_id: $option->store_id,
                name: $optionData['name'] ?? $option->name,
                description: $optionData['description'] ?? $option->description,
                price_cents: $optionData['price_cents'] ?? $option->price_cents,
                is_active: $optionData['is_active'] ?? $option->is_active,
            );

        }

        return $asArray ? array_map(fn($o) => $o->toArray(), $options) : $options;
    }
}
