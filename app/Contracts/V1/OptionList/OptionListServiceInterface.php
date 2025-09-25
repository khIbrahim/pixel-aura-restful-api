<?php

namespace App\Contracts\V1\OptionList;

use App\DTO\V1\OptionList\StoreOptionListDTO;
use App\DTO\V1\OptionList\UpdateOptionListDTO;
use App\Models\V1\OptionList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OptionListServiceInterface
{
    public function create(StoreOptionListDTO $data): OptionList;

    public function update(OptionList $optionList, UpdateOptionListDTO $data): OptionList;

    public function delete(OptionList $optionList): bool;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function findByStore(int $storeId, bool $withRelations = true): Collection;

    public function search(int $storeId, string $term, array $filters = []): Collection;

    public function bulkUpdateStatus(array $optionListIds, bool $isActive): int;

    public function attachToItem(OptionList $optionList, int $itemId, array $pivotData = []): void;

    public function detachFromItem(OptionList $optionList, int $itemId): void;
}
