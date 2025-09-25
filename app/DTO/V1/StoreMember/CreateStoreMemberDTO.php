<?php

namespace App\DTO\V1\StoreMember;

use App\Constants\V1\Defaults;
use App\Enum\StoreMemberRole;
use App\Models\V1\Store;

final readonly class CreateStoreMemberDTO
{

    public function __construct(
        public int             $storeId,
        public string          $name,
        public StoreMemberRole $role,
        public ?string         $pin,
        public bool            $isActive = true,
        public array           $meta,
        public ?array          $permissions = null
    ){}

    public static function fromRequest(Store $store, array $data): self
    {
        $name     = (string) $data['name'];
        $role     = StoreMemberRole::from(strtolower((string) $data['role']));
        $perms    = null; // Pas de permissions custom lors de la création.
        $pin      = (string) ($data['pin'] ?? Defaults::PIN);
        $isActive = (bool) ($data['is_active'] ?? Defaults::ACTIVE);
        $meta     = (array) ($data['meta'] ?? Defaults::META);

        return new self(
            storeId:     $store->id,
            name:        $name,
            role:        $role,
            pin:         $pin,
            isActive:    $isActive,
            meta:        $meta,
            permissions: $perms
        );
    }

}
