<?php

namespace App\Services\V1\StoreMember;

use App\Constants\V1\StoreTokenAbilities;
use App\Contracts\V1\StoreMember\StoreMemberRepositoryInterface;
use App\Contracts\V1\StoreMember\StoreMemberServiceInterface;
use App\DTO\V1\StoreMember\CreateStoreMemberDTO;
use App\DTO\V1\StoreMember\UpdateStoreMemberDTO;
use App\Enum\V1\StoreMemberRole;
use App\Exceptions\V1\StoreMember\StoreMemberCreationException;
use App\Exceptions\V1\StoreMember\StoreMemberDeletionException;
use App\Exceptions\V1\StoreMember\StoreMemberUpdateException;
use App\Models\V1\StoreMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class StoreMemberService implements StoreMemberServiceInterface
{

    public function __construct(
        private StoreMemberRepositoryInterface $storeMemberRepository,
    ){}

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->storeMemberRepository->list($storeId, $filters, $perPage);
    }

    public function create(CreateStoreMemberDTO $data): StoreMember
    {
        try {
            if ($data->role === StoreMemberRole::Owner){
                throw StoreMemberCreationException::cannotCreateOwner();
            }

            $pinHash     = Hash::make($data->pin);
            $permissions = $data->permissions ?? StoreTokenAbilities::getAbilitiesByRole($data->role);
            $code        = $this->storeMemberRepository->nextCodeNumber($data->storeId, $data->role);

            if ($this->storeMemberRepository->codeExists($data->storeId, $code)){
                throw StoreMemberCreationException::codeAlreadyExists($code);
            }

            /** @var StoreMember $storeMember */
            $storeMember = $this->storeMemberRepository->create(array_merge($data->toArray(), [
                'pin_hash'            => $pinHash,
                'permissions'         => $permissions,
                'code_number'         => $code,
                'pin_last_changed_at' => now()
            ]));

            Log::info("Store member créé", [
                'store_member_id' => $storeMember->id,
                'store_id'        => $data->storeId,
                'name'            => $storeMember->name,
                'role'            => $storeMember->role,
            ]);

            return $storeMember;
        } catch (Throwable $e){
            Log::error("Erreur lors de la création du store member", [
                'store_id' => $data->storeId,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            if($e instanceof StoreMemberCreationException){
                throw $e;
            }

            throw StoreMemberCreationException::default($e);
        }
    }

    /** @inheritDoc */
    public function update(StoreMember $storeMember, UpdateStoreMemberDTO $data): StoreMember
    {
        try {
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

            Log::info("Store member mis à jour", [
                'store_member_id' => $storeMember->id,
                'store_id'        => $storeMember->store_id,
                'name'            => $storeMember->name,
                'role'            => $storeMember->role,
            ]);

            return $storeMember->fresh(['user', 'store']);
        } catch (Throwable $e){
            Log::error("Erreur lors de la mise à jour du store member", [
                'store_member_id' => $storeMember->id,
                'store_id'        => $storeMember->store_id,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            if($e instanceof StoreMemberUpdateException){
                throw $e;
            }

            throw StoreMemberUpdateException::default($e);
        }
    }

    public function delete(StoreMember $storeMember): bool
    {
        try {
            if(request()->attributes->get('store_member')?->id === $storeMember->id){
                throw StoreMemberDeletionException::cannotDeleteSelf();
            }

            if($storeMember->role === StoreMemberRole::Owner){
                throw StoreMemberDeletionException::cannotDeleteOwner();
            }

            $deleted = $this->storeMemberRepository->delete($storeMember);

            if($deleted){
                Log::info("Store member supprimé", [
                    'store_member_id' => $storeMember->id,
                    'store_id'        => $storeMember->store_id,
                    'name'            => $storeMember->name,
                    'role'            => $storeMember->role,
                ]);
            }

            return $deleted;
        } catch (Throwable $e){
            Log::error("Erreur lors de la suppression du store member", [
                'store_member_id' => $storeMember->id,
                'store_id'        => $storeMember->store_id,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            if($e instanceof StoreMemberDeletionException){
                throw $e;
            }

            throw StoreMemberDeletionException::default($e);
        }
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
