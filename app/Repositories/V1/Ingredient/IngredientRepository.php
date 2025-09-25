<?php

namespace App\Repositories\V1\Ingredient;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Ingredient\UpdateIngredientDTO;
use App\Models\V1\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class IngredientRepository implements IngredientRepositoryInterface
{

    public function findOrCreateIngredient(CreateIngredientDTO $data): Ingredient
    {
        if ($data->id !== null) {
            $existingIngredient = $this->findIngredient($data->id);
            if ($existingIngredient) {
                return $existingIngredient;
            }
        }

        return $this->createIngredient($data);
    }

    public function findIngredient(int $id): ?Ingredient
    {
        return Ingredient::query()->find($id);
    }

    public function createIngredient(CreateIngredientDTO $data): Ingredient
    {
        return Ingredient::query()->create($data->toArray());
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

    public function update(Ingredient $ingredient, UpdateIngredientDTO $data): Ingredient
    {
        $ingredient->update(array_filter($data->toArray(), fn($value) => $value !== null));
        return $ingredient->fresh();
    }

    public function delete(Ingredient $ingredient): bool
    {
        return $ingredient->delete();
    }

    public function findIngredientsByIds(array $ids): Collection
    {
        return Ingredient::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }
}
