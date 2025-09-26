<?php

namespace App\Repositories\V1\Item;

use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\DTO\V1\Item\CreateVariantDTO;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Repositories\V1\BaseRepository;
use App\Traits\V1\Repository\HasAdvancedFiltering;
use App\Traits\V1\Repository\HasBatchOperations;
use App\Traits\V1\Repository\HasCaching;
use App\Traits\V1\Repository\ManagesRelations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{
    use HasCaching, ManagesRelations, HasBatchOperations, HasAdvancedFiltering;

    protected array $cacheTags = ['items'];

    public function createVariant(Item $item, CreateVariantDTO $data): ItemVariant
    {
        return $item->variants()->create($data->toArray());
    }

    public function bulkCreateVariants(Item $item, array $variants): void
    {
        $rows = [];

        /** @var CreateVariantDTO $variant */
        foreach ($variants as $variant) {
            $rows[] = array_merge($variant->toArray(), ['item_id' => $item->id, 'store_id' => $item->store_id, 'created_at' => now(), 'updated_at' => now()]);
        }

        ItemVariant::query()->insert($rows);

        $item->load('variants');
    }

    public function getItemsByCategory(int $categoryId): Collection
    {
        return Item::query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->with(['variants', 'ingredients', 'options'])
            ->get();
    }

    public function findItem(int $id, bool $withRelations = true): ?Item
    {
        $query = Item::query()->where('id', $id);

        if ($withRelations) {
            $query->with(['variants', 'ingredients', 'options', 'category', 'tax', 'media', 'creator']);
        }

        return $query->first();
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Item::query()->where('store_id', $storeId)->orderBy('name');

        if (! empty($filters['search'])) {
            $s = (string) $filters['search'];
            $query->where(function (Builder $q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('description', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%")
                  ->orWhereJsonContains('tags', $s);
            });
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (array_key_exists('category_id', $filters) && $filters['category_id'] !== null) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['with'])) {
            $with    = is_string($filters['with']) ? explode(',', $filters['with']) : $filters['with'];
            $allowed = ['variants', 'ingredients', 'options', 'category', 'tax', 'media', 'creator', 'media'];
            $query->with(array_intersect($allowed, $with));
        }

        return $query->paginate($perPage);
    }

    public function model(): string
    {
        return Item::class;
    }
}
