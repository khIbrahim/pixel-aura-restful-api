<?php

namespace App\Hydrators\V1\OptionList;

use App\DTO\V1\OptionList\UpdateOptionListDTO;
use App\Http\Requests\V1\OptionList\UpdateOptionListRequest;

readonly class UpdateOptionListHydrator
{
    public function fromRequest(UpdateOptionListRequest $request): UpdateOptionListDTO
    {
        $data = $request->validated();

        return new UpdateOptionListDTO(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            min_selections: $data['min_selections'] ?? null,
            max_selections: $data['max_selections'] ?? null,
            is_active: $data['is_active'] ?? null,
        );
    }
}
