<?php

namespace App\Repositories\V1\Option;

use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\DTO\V1\Option\CreateOptionDTO;
use App\Models\V1\Option;
use App\Repositories\V1\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OptionRepository extends BaseRepository implements OptionRepositoryInterface
{

    public function findOrCreateOption(CreateOptionDTO $data): Option
    {
        if ($data->id !== null) {
            /** @var null|Option $existingOption */
            $existingOption = $this->find($data->id);
            if ($existingOption) {
                return $existingOption;
            }
        }

        /** @var Option */
        return $this->create($data->toArray());
    }

    public function list(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = Option::query()->orderBy('name');

        if (isset($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (isset($filters['price_cents'])) {
            $operator = $filters['price_cents_operator'] ?? '=';
            if (!in_array($operator, ['=', '<', '>', '<=', '>='])) {
                $operator = '=';
            }
            $query->where('price_cents', $operator, $filters['price_cents']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        return $query->paginate($perPage);
    }

    public function findOptionsByIds(array $ids): Collection
    {
        return Option::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    public function getOptionPrice(int $id): ?int
    {
        /** @var null|Option $option */
        $option = $this->find($id);
        return $option?->price_cents;
    }

    public function model(): string
    {
        return Option::class;
    }
}
