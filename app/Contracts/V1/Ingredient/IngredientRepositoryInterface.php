<?php

namespace App\Contracts\V1\Ingredient;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\Models\V1\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IngredientRepositoryInterface extends BaseRepositoryInterface
{

    public function findOrCreateIngredient(CreateIngredientDTO $data): Ingredient;

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function findIngredientsByIds(array $ids): Collection;

    public function getIngredientPrice(int $id): ?int;

}
