<?php

namespace App\Services\V1\StoreMember;

use App\Constants\V1\StoreTokenAbilities;
use App\Contracts\V1\StoreMember\StoreMemberRepositoryInterface;
use App\Contracts\V1\StoreMember\StoreMemberServiceInterface;
use App\DTO\V1\StoreMember\CreateStoreMemberDTO;
use App\DTO\V1\StoreMember\UpdateStoreMemberDTO;
use App\Enum\StoreMemberRole;
use App\Exceptions\V1\CannotCreateOwnerStoreMember;
use App\Models\V1\StoreMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

readonly class StoreMemberService implements StoreMemberServiceInterface
{

    public function __construct(
        private StoreMemberRepositoryInterface $storeMemberRepository,
    ){}

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->storeMemberRepository->list($storeId, $filters, $perPage);
    }

    /**
     * @throws CannotCreateOwnerStoreMember
     */
    public function create(CreateStoreMemberDTO $data): StoreMember
    {
        if ($data->role === StoreMemberRole::Owner){
            throw new CannotCreateOwnerStoreMember("Un store ne peut pas contenir 2 owners");
        }

        $pinHash     = Hash::make($data->pin);
        $permissions = $data->permissions ?? StoreTokenAbilities::getAbilitiesByRole($data->role);

        /** @var StoreMember $storeMember */
        $storeMember = $this->storeMemberRepository->create(array_merge($data->toArray(), [
            'pin_hash'            => $pinHash,
            'permissions'         => $permissions,
            'code_number'         => $this->storeMemberRepository->nextCodeNumber($data->storeId, $data->role),
            'pin_last_changed_at' => now()
        ]));

        return $storeMember;
    }

    public function update(StoreMember $storeMember, UpdateStoreMemberDTO $data): StoreMember
    {
        $roleChanged = false;

        if ($data->name !== null){
            $storeMember->name = $data->name;
        }

        if($data->pin !== null){
            $storeMember->pin_hash            = Hash::make($data->pin);
            $storeMember->pin_last_changed_at = now();
        }

        if($data->isActive !== null){
            $storeMember->is_active = $data->isActive;
        }

        if($data->role !== null && $data->role !== $storeMember->role){
            $storeMember->role        = $data->role;
            $storeMember->code_number = $this->storeMemberRepository->nextCodeNumber($storeMember->store_id, $data->role);
            $roleChanged  = true;
        }

        if($data->meta !== null){
            $storeMember->meta = $data->meta;
        }

        if ($data->permissions !== null){
            $storeMember->permissions = array_values(array_unique($data->permissions));
        } elseif($roleChanged){
            $storeMember->permissions = StoreTokenAbilities::getAbilitiesByRole($storeMember->role);
        }

        $storeMember->save();

        if($roleChanged || $data->permissions !== null){
            $storeMember->tokens()->update([
                'abilities' => $storeMember->permissions
            ]);
        }

        return $storeMember->fresh(['user', 'store']);
    }

    public function delete(StoreMember $storeMember): bool
    {
        return $this->storeMemberRepository->delete($storeMember);
    }

    public function forceDelete(int $id): bool
    {
        return $this->storeMemberRepository->forceDelete($id);
    }

    public function restore(int $id): bool
    {
        return $this->storeMemberRepository->restore($id);
    }
}
