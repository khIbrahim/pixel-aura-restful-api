<?php

namespace App\DTO\V1\Media;

final readonly class MediaUploadOptions
{

    public function __construct(
        public string $collection,
        public ?string $disk = null,
        public bool $preserveOriginal = true,
        public bool $generateConversions = true,
        public bool $generateResponsive = false,
        public array $customProperties = [],
        public bool $signed = false,
    ){}

    public static function fromCategoryImage(): self
    {
        return new self(
            collection: 'category_images',
            disk: 's3',
            preserveOriginal: config('media-management.storage.preserve_original', true),
            generateConversions: true,
            generateResponsive: false,
            customProperties: [
                'type' => 'category',
            ],
        );
    }

    public static function fromItemImage(string $collection = 'main_image'): self
    {
        return new self(
            collection: $collection,
            disk: 's3',
            preserveOriginal: config('media-management.storage.preserve_original', true),
            generateConversions: true,
            generateResponsive: false,
            customProperties: ['type' => 'item'],
        );
    }

}
