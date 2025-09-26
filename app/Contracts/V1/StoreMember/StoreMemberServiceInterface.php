<?php

namespace App\Contracts\V1\StoreMember;

use App\DTO\V1\StoreMember\CreateStoreMemberDTO;
use App\DTO\V1\StoreMember\UpdateStoreMemberDTO;
use App\Models\V1\StoreMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StoreMemberServiceInterface
{

    public function create(CreateStoreMemberDTO $data): StoreMember;

    public function update(StoreMember $storeMember, UpdateStoreMemberDTO $data): StoreMember;

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function delete(StoreMember $storeMember): bool;

    public function forceDelete(int $id): bool;

    public function restore(int $id): bool;

}
