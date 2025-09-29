<?php

namespace App\Contracts\V1\OptionList;

use App\DTO\V1\OptionList\CreateOptionListDTO;
use App\DTO\V1\OptionList\UpdateOptionListDTO;
use App\Exceptions\V1\OptionList\OptionListCreationException;
use App\Exceptions\V1\OptionList\OptionListDeletionException;
use App\Exceptions\V1\OptionList\OptionListUpdateException;
use App\Models\V1\OptionList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OptionListServiceInterface
{

    /**
     * @throws OptionListCreationException
     */
    public function create(CreateOptionListDTO $data): OptionList;

    /**
     * @throws OptionListUpdateException
     */
    public function update(OptionList $optionList, UpdateOptionListDTO $data): OptionList;

    /**
     * @throws OptionListDeletionException
     */
    public function delete(OptionList $optionList): bool;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
