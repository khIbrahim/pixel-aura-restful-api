<?php

namespace App\Repositories\V1\Option;

use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\DTO\V1\Option\CreateOptionDTO;
use App\DTO\V1\Option\UpdateOptionDTO;
use App\Models\V1\Option;
use App\Traits\V1\Repository\CacheableRepositoryTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CachedOptionRepository implements OptionRepositoryInterface
{
    use CacheableRepositoryTrait;

    public function __construct(
        private readonly OptionRepositoryInterface $optionRepository
    ){}

    public function findOrCreateOption(CreateOptionDTO $data): Option
    {
        $key = "option:name:" . md5($data->name . $data->store_id);

        return $this->remember($key, function() use ($data) {
            return $this->optionRepository->findOrCreateOption($data);
        }, [$this->getTag()], 60);
    }

    protected function getTag(): string
    {
        return 'options';
    }

    public function findOption(int $id): ?Option
    {
        $key = "option:id:$id";
        return $this->remember($key, fn() => $this->optionRepository->findOption($id), [$this->getTag()]);
    }

    public function createOption(CreateOptionDTO $data): Option
    {
        $option = $this->optionRepository->createOption($data);

        $this->invalidate($option->store_id, [
            "options:store:$option->store_id"
        ]);

        return $option;
    }

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $storeId = $filters['store_id'] ?? null;

        $key = "options:store:$storeId:" . md5(serialize($filters)) . ":page:$perPage";

        $tags = [$this->getTag()];
        if ($storeId) {
            $tags[] = "store:$storeId";
        }

        return $this->remember(
            $key,
            fn() => $this->optionRepository->list($filters, $perPage),
            $tags
        );
    }

    public function updateOption(Option $option, UpdateOptionDTO $data): Option
    {
        $updatedOption = $this->optionRepository->updateOption($option, $data);

        $this->invalidate($option->store_id, [
            "options:store:$option->store_id",
            "option:id:$option->id"
        ]);

        return $updatedOption;
    }

    public function delete(Option $option): bool
    {
        $deleted = $this->optionRepository->delete($option);

        if($deleted) {
            $this->invalidate($option->store_id, [
                "options:store:$option->store_id",
                "option:id:$option->id"
            ]);
        }
        return $deleted;
    }

    public function findOptionsByIds(array $ids): Collection
    {
        $key = "options:ids:" . md5(serialize($ids));

        return $this->remember($key, function() use ($ids) {
            return $this->optionRepository->findOptionsByIds($ids);
        }, [$this->getTag()]);
    }
}
