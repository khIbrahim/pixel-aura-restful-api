<?php

namespace App\DTO\V1\Category;

use Illuminate\Contracts\Support\Arrayable;

final readonly class UpdateCategoryDTO implements Arrayable
{
    public function __construct(
        public ?string $name,
        public ?string $description,
        public ?array  $tags,
        public ?int    $position,
        public ?int    $parent_id,
        public ?bool   $is_active,
    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            tags: $data['tags'] ?? null,
            position: $data['position'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            is_active: array_key_exists('is_active', $data) ? (bool)$data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name'        => $this->name,
            'description' => $this->description,
            'tags'        => $this->tags,
            'position'    => $this->position,
            'parent_id'   => $this->parent_id,
            'is_active'   => $this->is_active,
        ], static fn($v) => $v !== null);
    }
}

