<?php

namespace App\Services\V1\Ingredient;

use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\Contracts\V1\Option\OptionServiceInterface;
use App\DTO\V1\Option\CreateOptionDTO;
use App\DTO\V1\Option\UpdateOptionDTO;
use App\Models\V1\Option;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class OptionService implements OptionServiceInterface
{

    public function __construct(
        private OptionRepositoryInterface $optionRepository,
    ){}

    public function list(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->optionRepository->list($filters, $perPage);
    }

    public function create(CreateOptionDTO $data): Option
    {
        return $this->optionRepository->createOption($data);
    }

    public function update(Option $option, UpdateOptionDTO $data): Option
    {
        return $this->optionRepository->updateOption($option, $data);
    }

    public function delete(Option $option): bool
    {
        $option->items()->detach($option);

        return $this->optionRepository->delete($option);
    }
}
