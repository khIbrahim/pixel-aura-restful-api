<?php

namespace App\Contracts\V1\Media;

use App\DTO\V1\Media\MediaUploadOptions;
use App\Support\Results\MediaResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

interface MediaManagerInterface
{

    public function uploadImage(Model $model, UploadedFile|string $file, MediaUploadOptions $options): MediaResult;

    public function replaceImage(Model $model, UploadedFile|string $file, MediaUploadOptions $options): MediaResult;

    public function deleteImage(Model $model, string $collection, ?int $mediaId = null): MediaResult;

}
