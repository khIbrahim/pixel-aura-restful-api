<?php

namespace App\DTO\V1\StoreMember;

use App\Enum\StoreMemberRole;

final readonly class UpdateStoreMemberDTO
{

    public function __construct(
        public ?string          $name = null,
        public ?string          $pin = null,
        public ?StoreMemberRole $role = null,
        public ?bool            $isActive = null,
        public ?array           $meta = null,
        public ?array           $permissions = null,
    ){}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            pin: $data['pin'] ?? null,
            role: array_key_exists('role', $data) ? StoreMemberRole::from(strtolower((string) $data['role'])) : null,
            isActive: $data['is_active'] ?? null,
            meta: $data['meta'] ?? null,
            permissions: $data['permissions'] ?? null,
        );
    }

}
