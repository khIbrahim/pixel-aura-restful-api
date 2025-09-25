<?php

namespace App\Actions\StoreMember\V1;

use App\DTO\V1\StoreMember\CreateStoreMemberDTO;
use App\Enum\StoreMemberRole;
use App\Exceptions\V1\CannotCreateOwnerStoreMember;
use App\Models\V1\StoreMember;
use App\Services\V1\StoreMember\StoreMemberCodeService;
use App\Services\V1\StoreMember\StoreMemberPermissionsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final readonly class CreateStoreMember
{

    public function __construct(
        private StoreMemberCodeService $codeService,
        private StoreMemberPermissionsService $storeMemberPermissions
    ){}

    public function __invoke(CreateStoreMemberDTO $data): StoreMember
    {
        return DB::transaction(function () use ($data) {
            if ($data->role === StoreMemberRole::Owner){
                throw new CannotCreateOwnerStoreMember("Un store ne peut pas contenir 2 owners");
            }

            $pinHash     = Hash::make($data->pin);
            $permissions = $data->permissions ?? $this->storeMemberPermissions->getByRole($data->role);

            return StoreMember::create([
                'store_id'    => $data->storeId,
                'name'        => $data->name,
                'role'        => $data->role,
                'pin_hash'    => $pinHash,
                'is_active'   => $data->isActive,
                'permissions' => $permissions,
                'code_number' => $this->codeService->next($data->storeId, $data->role),
                'meta'        => $data->meta,

                'pin_last_changed_at' => now()
            ]);
        });
    }

}
