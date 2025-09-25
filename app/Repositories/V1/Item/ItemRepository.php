<?php

namespace App\Repositories\V1\Item;

use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Item\CreateVariantDTO;
use App\DTO\V1\Option\CreateOptionDTO;
use App\Models\V1\Ingredient;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Models\V1\Option;
use App\Repositories\V1\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{

    public function __construct()
    {
    }

    public function createItem(CreateItemDTO $data): Item
    {
        return Item::query()->create($data->toArray());
    }

    public function createVariant(Item $item, CreateVariantDTO $data): ItemVariant
    {
        return $item->variants()->create($data->toArray());
    }

    public function attachIngredient(Item $item, Ingredient $ingredient, CreateIngredientDTO $data): void
    {
        $item->ingredients()->attach($ingredient->id, [
            'is_mandatory'        => $data->is_mandatory,
            'is_active'           => $data->is_active,
            'store_id'            => $data->store_id,
            'name'                => $data->name,
            'is_allergen'         => $data->is_allergen,
            'cost_per_unit_cents' => $data->cost_per_unit_cents,
        ]);
    }

    public function attachOption(Item $item, Option $option, CreateOptionDTO $data): void
    {
        $item->options()->attach($option->id, [
            'name'        => $data->name,
            'store_id'    => $data->store_id,
            'price_cents' => $data->price_cents,
            'is_active'   => $data->is_active,
        ]);
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

   public function attachOptions(Item $item, array $options): Collection
   {
       return DB::transaction(function () use ($item, $options) {
           $values = implode(", ", array_map(
               function($option) use ($item) {
                   $id          = (int) $option['option_id'];
                   $storeId     = $item->store_id;
                   $name        = addslashes($option['name']);
                   $description = addslashes($option['description'] ?? '');
                   $priceCents  = (int) $option['price_cents'];
                   $isActive    = $option['is_active'] ? 1 : 0;
                   $createdAt   = $updatedAt = now()->toDateTimeString();
                   return "($item->id, $storeId, $id, '$name', '$description', $priceCents, $isActive, '$createdAt', '$updatedAt')";
               },
               $options
           ));

           $sql = "
               INSERT INTO item_options (item_id, store_id, option_id, name, description, price_cents, is_active, created_at, updated_at)
               VALUES $values
               ON DUPLICATE KEY UPDATE
                   name        = VALUES(name),
                   description = VALUES(description),
                   price_cents = VALUES(price_cents),
                   is_active   = VALUES(is_active),
                   updated_at  = VALUES(updated_at)
           ";

           DB::statement($sql);

           $item->unsetRelation('options');
           return $item->options()
               ->withPivot(['name', 'description', 'price_cents', 'is_active'])
               ->get();
       });
   }

    public function model(): string
    {
        return Item::class;
    }
}
