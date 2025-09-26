<?php

namespace App\Repositories\V1\StoreMember;

use App\Contracts\V1\StoreMember\StoreMemberRepositoryInterface;
use App\Enum\StoreMemberRole;
use App\Models\V1\StoreMember;
use App\Models\V1\StoreMemberCounter;
use App\Repositories\V1\BaseRepository;
use App\Traits\V1\Repository\HasAdvancedFiltering;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StoreMemberRepository extends BaseRepository implements StoreMemberRepositoryInterface
{
    use HasAdvancedFiltering;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->query()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->with(['user', 'role']);

        $query = $this->applyAdvancedFilters($filters, $query);

        if (isset($filters['role'])) {
            $query->whereHas('role', function ($q) use ($filters) {
                $q->where('role', 'like', '%' . $filters['role'] . '%');
            });
        }

        if (isset($filters['sort'])) {
            $query = $this->applySorting($query, $filters['sort']);
        } else {
            $query->orderBy('name');
        }

        return $query->paginate($perPage);
    }

    public function model(): string
    {
        return StoreMember::class;
    }

    public function nextCodeNumber(int $storeId, null|StoreMemberRole|string $role = null): int
    {
        return DB::transaction(function () use ($storeId, $role) {
            $role = is_string($role) ? StoreMemberRole::from($role) : $role;

            /** @var StoreMemberCounter $counter */
            $counter = StoreMemberCounter::query()
                ->where('store_id', $storeId)
                ->when($role, fn($q) => $q->where('role', $role->value))
                ->lockForUpdate()
                ->first();

            if(! $counter) {
                $counter = StoreMemberCounter::query()
                    ->create([
                        'store_id' => $storeId,
                        'role' => $role->value,
                        'next_code' => 1
                    ]);
            }

            $code = $counter->next_code;

            $counter->increment('next_code');
            $counter->save();

            return $code;
        });
    }
}
