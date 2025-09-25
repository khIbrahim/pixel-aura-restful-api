<?php

namespace App\Traits\V1\Media;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Model
 */
trait HasOptimizedMedia
{
    public function getFirstMediaResource(string $collection): ?array
    {
        $media = $this->getFirstMedia($collection);
        return $media ? $this->formatMediaResource($media) : null;
    }

    public function getMediaResources(string $collection): array
    {
        return $this->getMedia($collection)
            ->map(fn ($media) => $this->formatMediaResource($media))
            ->toArray();
    }

    private function formatMediaResource(Media $media): array
    {
        $urls = ['original' => $media->getUrl()];

        foreach (config('media.conversions', []) as $name => $settings) {
            if ($media->hasGeneratedConversion($name)) {
                $urls[$name] = $media->getUrl($name);
            }
        }

        return [
            'id'   => $media->id,
            'type' => $media->getCustomProperty('type', 'image'),
            'alt'  => $media->getCustomProperty('alt', $this->name ?? ''),
            'urls' => $urls,
        ];
    }
}
