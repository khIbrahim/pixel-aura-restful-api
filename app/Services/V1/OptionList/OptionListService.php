<?php

namespace App\Services\V1\OptionList;

use App\Contracts\V1\OptionList\OptionListRepositoryInterface;
use App\Contracts\V1\OptionList\OptionListServiceInterface;
use App\DTO\V1\OptionList\CreateOptionListDTO;
use App\DTO\V1\OptionList\UpdateOptionListDTO;
use App\Models\V1\OptionList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

readonly class OptionListService implements OptionListServiceInterface
{
    public function __construct(
        private OptionListRepositoryInterface $repository,
    ) {}

    public function create(CreateOptionListDTO $data): OptionList
    {
        /** @var OptionList $optionList */
        $optionList = $this->repository->create($data->toArray());
        $this->clearCache($data->store_id);
        return $optionList->load('options');
    }

    public function update(OptionList $optionList, UpdateOptionListDTO $data): OptionList
    {
        $updatedOptionList = $this->repository->update($optionList, $data->toArray());
        assert($updatedOptionList instanceof OptionList);
        $this->clearCache($optionList->store_id);
        $updatedOptionList->load('options');
        return $updatedOptionList;
    }

    public function delete(OptionList $optionList): bool
    {
        $storeId = $optionList->store_id;
        $result = $this->repository->delete($optionList);
        $this->clearCache($storeId);
        return $result;
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->filterForPos($storeId, $filters, $perPage);
    }

    private function clearCache(int $storeId): void
    {
        Cache::tags(['option_lists'])->flush();
        Cache::forget("option_lists.store.$storeId");
    }
}
