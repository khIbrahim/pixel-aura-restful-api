<?php

namespace App\Repositories\V1\Ingredient;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Ingredient\UpdateIngredientDTO;
use App\Models\V1\Ingredient;
use App\Traits\V1\Repository\CacheableRepositoryTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CachedIngredientRepository implements IngredientRepositoryInterface
{
    use CacheableRepositoryTrait;

    public function __construct(
        private readonly IngredientRepositoryInterface $ingredientRepository
    ){}

    public function findOrCreateIngredient(CreateIngredientDTO $data): Ingredient
    {
        $key = "ingredient:name:" . md5($data->name . $data->store_id);

        return $this->remember($key, function() use ($data) {
            return $this->ingredientRepository->findOrCreateIngredient($data);
        }, [$this->getTag()], 60);
    }

    public function findIngredient(int $id): ?Ingredient
    {
        $key = "ingredient:id:$id";

        return $this->remember($key, function() use ($id) {
            return $this->ingredientRepository->findIngredient($id);
        }, [$this->getTag()]);
    }

    public function createIngredient(CreateIngredientDTO $data): Ingredient
    {
        $ingredient = $this->ingredientRepository->createIngredient($data);

        $this->invalidate($ingredient->store_id, [
            "ingredients:store:$ingredient->store_id"
        ]);

        return $ingredient;
    }

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $storeId = $filters['store_id'] ?? null;

        $key = "ingredients:store:$storeId:" . md5(serialize($filters)) . ":page:$perPage";

        $tags = [$this->getTag()];
        if ($storeId) {
            $tags[] = "store:$storeId";
        }

        return $this->remember(
            $key,
            fn() => $this->ingredientRepository->list($filters, $perPage),
            $tags
        );
    }

    protected function getTag(): string
    {
        return 'ingredients';
    }

    public function update(Ingredient $ingredient, UpdateIngredientDTO $data): Ingredient
    {
        $updatedIngredient = $this->ingredientRepository->update($ingredient, $data);
        $this->invalidate($ingredient->store_id, ["ingredients:store:$ingredient->store_id"]);
        return $updatedIngredient;
    }

    public function delete(Ingredient $ingredient): bool
    {
        $deleted = $this->ingredientRepository->delete($ingredient);
        if($deleted) {
            $this->invalidate($ingredient->store_id, ["ingredients:store:$ingredient->store_id"]);
        }
        return $deleted;
    }

    public function findIngredientsByIds(array $ids): Collection
    {
        $key = "ingredients:ids:" . md5(serialize($ids));

        return $this->remember($key, function() use ($ids) {
            return $this->ingredientRepository->findIngredientsByIds($ids);
        }, [$this->getTag()]);
    }
}
