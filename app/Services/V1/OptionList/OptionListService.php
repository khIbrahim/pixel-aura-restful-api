<?php

namespace App\Services\V1\OptionList;

use App\Contracts\V1\OptionList\OptionListRepositoryInterface;
use App\Contracts\V1\OptionList\OptionListServiceInterface;
use App\DTO\V1\OptionList\StoreOptionListDTO;
use App\DTO\V1\OptionList\UpdateOptionListDTO;
use App\Models\V1\OptionList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

readonly class OptionListService implements OptionListServiceInterface
{
    public function __construct(
        private OptionListRepositoryInterface $repository,
    ) {}

    public function create(StoreOptionListDTO $data): OptionList
    {
        $optionList = $this->repository->create($data->toArray());
        $this->clearCache($data->storeId);
        return $optionList->load('options');
    }

    public function update(OptionList $optionList, UpdateOptionListDTO $data): OptionList
    {
        $updatedOptionList = $this->repository->update($optionList, $data->toArray());
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

    public function findByStore(int $storeId, bool $withRelations = true): Collection
    {
        return $withRelations ? $this->repository->findByStoreWithRelations($storeId) : $this->repository->findManyBy('store_id', $storeId);
    }

    public function search(int $storeId, string $term, array $filters = []): Collection
    {
        return $this->repository->searchOptionLists($storeId, $term, $filters)->get();
    }

    public function bulkUpdateStatus(array $optionListIds, bool $isActive): int
    {
        $result = $this->repository->bulkUpdateStatus($optionListIds, $isActive);

        $optionLists = $this->repository->findMany($optionListIds, ['store_id']);
        $storeIds = $optionLists->pluck('store_id')->unique();

        foreach ($storeIds as $storeId) {
            $this->clearCache($storeId);
        }

        return $result;
    }

    public function attachToItem(OptionList $optionList, int $itemId, array $pivotData = []): void
    {
        $this->repository->attachRelation($optionList->id, 'items', $itemId, $pivotData);
        $this->clearCache($optionList->store_id);
    }

    public function detachFromItem(OptionList $optionList, int $itemId): void
    {
        $this->repository->detachRelation($optionList->id, 'items', $itemId);
        $this->clearCache($optionList->store_id);
    }

    private function clearCache(int $storeId): void
    {
        Cache::tags(['option_lists'])->flush();
        Cache::forget("option_lists.store.{$storeId}");
    }
}
