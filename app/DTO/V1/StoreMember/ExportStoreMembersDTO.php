<?php

namespace App\DTO\V1\StoreMember;

use App\Enum\StoreMemberRole;
use JsonSerializable;

final readonly class ExportStoreMembersDTO
{

    public function __construct(
        public bool   $async     = false,
        public string $format    = 'csv',
        public array  $filters   = [],
        public ?int    $priority = null,
    ){}

    public static function fromRequest(array $data): self
    {
        return new self(
            async:    $data['async'] ?? false,
            format:   $data['format'] ?? 'csv',
            filters:  self::parseFilters($data),
            priority: $data['priority'] ?? null,
        );
    }

    public static function parseFilters(array $filters): array
    {
        if (isset($filters['role'])){
            $filters['role'] = StoreMemberRole::from((string) $filters['role']);
        }

        if (isset($filters['is_active'])){
            $filters['is_active'] = (bool) $filters['is_active'];
        }

        return $filters;
    }
}
