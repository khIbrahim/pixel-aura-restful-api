<?php

namespace App\Repositories\V1\OptionList;

use App\Contracts\V1\OptionList\OptionListRepositoryInterface;
use App\Models\V1\OptionList;
use App\Repositories\V1\BaseRepository;
use App\Traits\V1\Repository\HasAdvancedFiltering;
use App\Traits\V1\Repository\HasBatchOperations;
use App\Traits\V1\Repository\HasCaching;
use App\Traits\V1\Repository\ManagesRelations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OptionListRepository extends BaseRepository implements OptionListRepositoryInterface
{
    use ManagesRelations, HasCaching, HasAdvancedFiltering, HasBatchOperations;

    protected array $cacheTags = ['option_lists'];

    public function model(): string
    {
        return OptionList::class;
    }

    public function findByStoreWithRelations(int $storeId, array $relations = ['options']): Collection
    {
        return $this->cacheQuery(
            $this->with($relations)->where('store_id', $storeId)->where('is_active', true),
            "store.$storeId.with." . implode('.', $relations),
            1800
        );
    }

    public function filterForPos(int $storeId, array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->query()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->with(['options']);

        $query = $this->applyAdvancedFilters($filters, $query);

        if (isset($filters['sort'])) {
            $query = $this->applySorting($query, $filters['sort']);
        } else {
            $query->orderBy('name');
        }

        return $query->paginate($perPage);
    }

    public function searchOptionLists(int $storeId, string $term, array $filters = []): Builder
    {
        $query = $this->query()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%$term%")
                  ->orWhere('description', 'LIKE', "%$term%");
            });

        if (! empty($filters)) {
            $query = $this->applyAdvancedFilters($filters, $query);
        }

        return $query->with(['options']);
    }

    public function findWithMinimumOptions(int $storeId, int $minOptions = 1): Collection
    {
        return $this->allCached()
            ->where('store_id', $storeId)
            ->filter(fn($optionList) => $optionList->options()->count() >= $minOptions);
    }

    public function bulkUpdateStatus(array $optionListIds, bool $isActive): int
    {
        return $this->batchUpdate(
            ['id' => $optionListIds],
            ['is_active' => (int) $isActive],
            500
        );
    }

    public function scopeActive(Builder $query, bool $active = true): Builder
    {
        return $query->where('is_active', $active);
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }
}
