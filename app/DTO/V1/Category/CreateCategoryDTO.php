<?php

namespace App\DTO\V1\Category;

use Illuminate\Contracts\Support\Arrayable;

final readonly class CreateCategoryDTO implements Arrayable
{

    public function __construct(
        public string      $name,
        public ?string     $description,
        public ?array      $tags,
        public ?int        $position, // rendu nullable
        public ?int        $parent_id,
        public bool        $is_active,
        public ?int        $store_id = null,
        public ?string     $slug = null,
    ){}

    public static function fromRequest(array $data): self
    {
        return new self(
            name:        $data['name'],
            description: $data['description'] ?? null,
            tags:        $data['tags'] ?? null,
            position:    $data['position'] ?? null,
            parent_id:   $data['parent_id'] ?? null,
            is_active:   $data['is_active'] ?? true,
            store_id:    $data['store_id'] ?? null,
            slug:        $data['slug'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'description' => $this->description,
            'tags'        => $this->tags,
            'position'    => $this->position,
            'parent_id'   => $this->parent_id,
            'is_active'   => $this->is_active,
            'store_id'    => $this->store_id,
            'slug'        => $this->slug,
        ];
    }
}
