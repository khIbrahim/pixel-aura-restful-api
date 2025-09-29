<?php

namespace App\Exceptions\V1\Media;

use App\Exceptions\V1\BaseApiException;

class UrlImageException extends BaseApiException
{

    protected $code             = 400;
    protected string $errorType = 'URL_IMAGE_ERROR';

    public static function invalidUrl(string $url): self
    {
        return new self("URL invalide ou inaccessible: $url");
    }

    public static function downloadFailed(string $url, string $reason): self
    {
        return new self("Échec du téléchargement depuis $url: $reason");
    }

    public static function domainBlocked(string $domain): self
    {
        return new self("Domaine bloqué: $domain");
    }

    public static function fileTooLarge(int $size, int $maxSize): self
    {
        return new self("Fichier trop volumineux: $size bytes (max: $maxSize bytes)");
    }

    public static function invalidImageFormat(string $mimeType): self
    {
        return new self("Format d'image invalide: $mimeType");
    }
}
