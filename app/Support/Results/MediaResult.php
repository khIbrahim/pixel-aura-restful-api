<?php

namespace App\Support\Results;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaResult extends Result
{

    private ?Media $media = null;

    public function __construct(bool $success, ?string $message = null, array $errors = [], mixed $data = null, ?Media $media = null)
    {
        parent::__construct($success, $message, $errors, $data);
        $this->media = $media;
    }

    public static function successUpload(?Media $media, ?string $message = null, array $urls = []): self
    {
        return new self(true, $message ?? "L'image a bien été uploaded", [], $urls, $media);
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public static function successDelete(string $message, array $urls = []): self
    {
        return new self(true, $message, [], $urls);
    }

}
