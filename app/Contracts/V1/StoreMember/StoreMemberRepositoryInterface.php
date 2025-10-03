<?php

namespace App\Contracts\V1\StoreMember;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\Enum\V1\StoreMemberRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StoreMemberRepositoryInterface extends BaseRepositoryInterface
{

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function nextCodeNumber(int $storeId, null|StoreMemberRole|string $role = null): int;

    public function codeExists(int $storeId, int $code): bool;

}
