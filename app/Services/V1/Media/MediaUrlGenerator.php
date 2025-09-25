<?php

namespace App\Services\V1\Media;

use App\DTO\V1\Media\MediaUploadOptions;
use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaUrlGenerator
{
    private const DEFAULT_TTL = 15; // minutes
    private const SUPPORTED_CONVERSIONS = ['thumbnail', 'banner'];

    public function all(Media $media, bool $signed = false, int $ttl = self::DEFAULT_TTL): array
    {
        $urls = [
            'original' => $this->for($media, null, $signed, $ttl),
        ];

        foreach (self::SUPPORTED_CONVERSIONS as $conv) {
            if ($media->hasGeneratedConversion($conv)) {
                $urls[$conv] = $this->for($media, $conv, $signed, $ttl);
            }
        }

        return $urls;
    }

    public function for(Media $media, ?string $conversion = null, bool $signed = false, int $ttl = self::DEFAULT_TTL): string
    {
        $params = ['media' => $media->id];
        if ($conversion) {
            $params['conversion'] = $conversion;
        }

        return $signed
            ? URL::temporarySignedRoute('media.show', now()->addMinutes($ttl), $params)
            : route('media.show', $params, true);
    }

    public function generateUrls(Media $media, MediaUploadOptions $options): array
    {
        $signed = $options->signed ?? false;
        $ttl    = $options->ttl ?? self::DEFAULT_TTL;

        $urls = [
            'original' => $this->for($media, null, $signed, $ttl),
        ];

        foreach (self::SUPPORTED_CONVERSIONS as $type) {
            if ($media->hasGeneratedConversion($type)) {
                $urls[$type] = $this->for($media, $type, $signed, $ttl);
            }
        }

        if ($options->generateResponsive && $media->responsive_images) {
            $urls['responsive'] = $media->responsive_images;
        }

        return $urls;
    }
}
