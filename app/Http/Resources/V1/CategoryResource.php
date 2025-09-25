<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Category;
use App\Services\V1\Media\MediaUrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'tags'        => $this->tags,
            'position'    => $this->position,
            'parent_id'   => $this->parent_id,
            'parent'      => $this->whenLoaded('parent', fn() => $this->parent ? new CategoryResource($this->parent) : null),
            'children'    => CategoryResource::collection($this->whenLoaded('children')),
            'is_active'   => $this->is_active,
            'image'       => $this->when($this->relationLoaded('media'), fn() => $this->formatImageObject()),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            'children_count' => $this->when($this->relationLoaded('children'), fn() => $this->children->count()),
            'items_count'    => $this->when($this->relationLoaded('items'), fn() => $this->items->count()),
            'items'          => ItemResource::collection($this->whenLoaded('items')),
        ];
    }

    public function formatImageObject(): ?array
    {
        $media = $this->getFirstMedia('category_images');
        if (!$media) {
            return null;
        }

        $urlGenerator = app(MediaUrlGenerator::class);

        $urls = $urlGenerator->getCdnUrls($media);

        return [
            'id'          => $media->id,
            'type'        => $media->getCustomProperty('type', 'thumbnail'),
            'alt'         => $media->getCustomProperty('alt', $this->name),
            'url'         => $urls['url'] ?? null,
            'thumb_url'   => $urls['thumb_url'] ?? null,
            'medium_url'  => $urls['medium_url'] ?? null,
            'optimized_url' => $urls['optimized_url'] ?? null,
            'file_name'   => $media->file_name,
            'mime_type'   => $media->mime_type,
            'size'        => $media->size,
            'human_readable_size' => $media->human_readable_size,
            'created_at'  => $media->created_at,
            'updated_at'  => $media->updated_at,

            'conversions' => $this->getAvailableConversions($media),
            'dimensions'  => $this->getImageDimensions($media),
        ];
    }

    private function getAvailableConversions($media): array
    {
        $conversions = [];

        $availableConversions = ['thumbnail', 'medium', 'optimized'];

        foreach ($availableConversions as $conversion) {
            if ($media->hasGeneratedConversion($conversion)) {
                $conversions[] = [
                    'name' => $conversion,
                    'url' => app(MediaUrlGenerator::class)->for($media, $conversion),
                ];
            }
        }

        return $conversions;
    }

    private function getImageDimensions($media): ?array
    {
        $customProperties = $media->custom_properties;

        if (isset($customProperties['width']) && isset($customProperties['height'])) {
            return [
                'width' => $customProperties['width'],
                'height' => $customProperties['height'],
                'aspect_ratio' => round($customProperties['width'] / $customProperties['height'], 2),
            ];
        }

        return null;
    }
}
