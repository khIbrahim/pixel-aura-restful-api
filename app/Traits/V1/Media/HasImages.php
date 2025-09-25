<?php

namespace App\Traits\V1\Media;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasImages
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_image')
            ->singleFile();

        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(300)
            ->height(300)
            ->nonQueued();

        $this->addMediaConversion('banner')
            ->width(1200)
            ->height(600)
            ->nonQueued();

        $this->addMediaConversion('icon')
            ->width(64)
            ->height(64)
            ->nonQueued();
    }

    public function getImagesAttribute(): array
    {
        $mainImage = $this->getFirstMedia('main_image');
        $gallery   = $this->getMedia('gallery');

        return [
            'main' => $mainImage ? [
                'original'  => $mainImage->getUrl(),
                'thumbnail' => $mainImage->getUrl('thumbnail'),
                'banner'    => $mainImage->getUrl('banner'),
                'icon'      => $mainImage->getUrl('icon'),
            ] : null,
            'gallery' => $gallery->map(fn (Media $media) => [
                'id'        => $media->id,
                'original'  => $media->getUrl(),
                'thumbnail' => $media->getUrl('thumbnail'),
                'banner'    => $media->getUrl('banner'),
                'icon'      => $media->getUrl('icon'),
            ])->all(),
        ];
    }

    public function getMainImage(): ?Media
    {
        return $this->getFirstMedia('main_image');
    }

    public function getGalleryImages(): Collection
    {
        return $this->getMedia('gallery')->collect();
    }

}
