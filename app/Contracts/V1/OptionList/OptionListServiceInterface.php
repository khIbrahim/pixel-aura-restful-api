<?php

namespace App\Contracts\V1\OptionList;

use App\DTO\V1\OptionList\CreateOptionListDTO;
use App\DTO\V1\OptionList\UpdateOptionListDTO;
use App\Models\V1\OptionList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OptionListServiceInterface
{
    public function create(CreateOptionListDTO $data): OptionList;

    public function update(OptionList $optionList, UpdateOptionListDTO $data): OptionList;

    public function delete(OptionList $optionList): bool;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
