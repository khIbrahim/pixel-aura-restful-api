<?php

namespace App\Contracts\V1\Option;

use App\DTO\V1\Option\CreateOptionDTO;
use App\DTO\V1\Option\UpdateOptionDTO;
use App\Models\V1\Option;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OptionRepositoryInterface
{

    public function findOrCreateOption(CreateOptionDTO $data): Option;

    public function findOption(int $id): ?Option;

    public function createOption(CreateOptionDTO $data): Option;

    public function list(array $filters, int $perPage = 25): LengthAwarePaginator;

    public function updateOption(Option $option, UpdateOptionDTO $data): Option;

    public function delete(Option $option): bool;

    public function findOptionsByIds(array $ids): Collection;

}
