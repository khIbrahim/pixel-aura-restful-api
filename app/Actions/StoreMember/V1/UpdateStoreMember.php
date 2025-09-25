<?php

namespace App\Actions\StoreMember\V1;

use App\DTO\V1\StoreMember\UpdateStoreMemberDTO;
use App\Models\V1\StoreMember;
use App\Services\V1\StoreMember\StoreMemberCodeService;
use App\Services\V1\StoreMember\StoreMemberPermissionsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateStoreMember
{

    public function __construct(
        private readonly StoreMemberCodeService $memberCodeService,
        private readonly StoreMemberPermissionsService $memberPermissions
    ){}

    public function __invoke(StoreMember $member, UpdateStoreMemberDTO $data)
    {
        return DB::transaction(function () use ($member, $data) {
            $roleChanged = false;

            if ($data->name !== null){
                $member->name = $data->name;
            }

            if($data->pin !== null){
                $member->pin_hash            = Hash::make($data->pin);
                $member->pin_last_changed_at = now();
            }

            if($data->isActive !== null){
                $member->is_active = $data->isActive;
            }

            if($data->role !== null && $data->role !== $member->role){
                $member->role        = $data->role;
                $member->code_number = $this->memberCodeService->next($member->store_id, $data->role);
                $roleChanged  = true;
            }

            if($data->meta !== null){
                $member->meta = $data->meta;
            }

            if ($data->permissions !== null){
                $member->permissions = array_values(array_unique($data->permissions));
            } elseif($roleChanged){
                $member->permissions = $this->memberPermissions->getByRole($member->role);
            }

            $member->save();

            if($roleChanged || $data->permissions !== null){
                $member->tokens()->update([
                    'abilities' => $member->permissions
                ]);
            }

            return $member->fresh(['user', 'store']);
        });
    }

}
