<?php

namespace App\Media\PathGenerator\V1;

use App\Contracts\V1\Media\DefinesMediaPath;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class PixelPathGenerator implements PathGenerator
{

    public function getPath(Media $media): string
    {
        $model = $media->model;
        if($model instanceof DefinesMediaPath){
            return $model->getMediaBasePath();
        }

        return $media->collection_name . '/' . $this->getBasePath($media);
    }

    protected function getBasePath(Media $media): string
    {
        $prefix = config('media-library.prefix', '');

        if ($prefix !== '') {
            return $prefix.'/'.$media->getKey();
        }

        return $media->getKey();
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}
