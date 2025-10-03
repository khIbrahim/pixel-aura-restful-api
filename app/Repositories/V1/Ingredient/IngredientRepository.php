<?php

namespace App\Repositories\V1\Ingredient;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\Models\V1\Ingredient;
use App\Repositories\V1\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class IngredientRepository extends BaseRepository implements IngredientRepositoryInterface
{

    public function findOrCreateIngredient(CreateIngredientDTO $data): Ingredient
    {
        if ($data->id !== null) {
            /** @var null|Ingredient $existingIngredient */
            $existingIngredient = $this->find($data->id);
            if ($existingIngredient) {
                return $existingIngredient;
            }
        }

        /** @var Ingredient */
        return $this->create($data->toArray());
    }

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Ingredient::query()->orderBy('name');

        if(array_key_exists('store_id', $filters) && $filters['store_id'] !== null) {
            $query->where('store_id', (int) $filters['store_id']);
        }

        if(array_key_exists('is_allergen', $filters) && $filters['is_allergen'] !== null) {
            $query->where('is_allergen', (bool) $filters['is_allergen']);
        }

        if(array_key_exists('is_mandatory', $filters) && $filters['is_mandatory'] !== null) {
            $query->where('is_mandatory', (bool) $filters['is_mandatory']);
        }

        if(array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $search = $filters['search'] ?? null;
        if(! empty($search)) {
            $s = (string)$search;
            $query->where('name', 'like', '%' . $s . '%');
        }

        return $query->paginate($perPage);
    }

    public function findIngredientsByIds(array $ids): Collection
    {
        return Ingredient::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    public function model(): string
    {
        return Ingredient::class;
    }

    public function getIngredientPrice(int $id): ?int
    {
        /** @var null|Ingredient $ingredient */
        $ingredient = $this->find($id);
        return $ingredient?->price_cents;
    }
}
