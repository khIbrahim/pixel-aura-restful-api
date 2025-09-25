<?php

namespace App\Hydrators\V1\Option;

use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\DTO\V1\Option\OptionPivotDTO;
use App\Http\Requests\V1\Items\AttachOptionsRequest;
use App\Models\V1\Option;
use Illuminate\Support\Facades\Cache;

readonly class OptionsAttachHydrator
{

    public function __construct(
        private OptionRepositoryInterface $optionRepository
    ){}

    /**
     * @param AttachOptionsRequest $request
     * @return OptionPivotDTO[]
     */
    public function fromRequest(AttachOptionsRequest $request): array
    {
        $data       = $request->validated();
        $optionsIds = array_column($data['options'], 'id');

        $existingOptions = $this->optionRepository->findOptionsByIds($optionsIds);

        $options = [];
        foreach ($data['options'] as $optionData) {
            $id     = (int) $optionData['id'];
            /** @var Option $option */
            $option = $existingOptions->get($id); // ça existe vu que j'ai fait la validation avec le form request

            $cacheKey = 'option_'.$id.'_pivot';

            $options[$id] = Cache::remember($cacheKey, 300, function() use ($option, $optionData) {
                return new OptionPivotDTO(
                    option_id: $option->id,
                    store_id: $option->store_id,
                    name: $optionData['name'] ?? $option->name,
                    description: $optionData['description'] ?? $option->description,
                    price_cents: $optionData['price_cents'] ?? $option->price_cents,
                    is_active: $optionData['is_active'] ?? $option->is_active,
                );
            });
        }

        return $options;
    }

}
