<?php

namespace App\Exceptions\V1\Catalog;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class CatalogCompilationException extends BaseApiException
{

    protected int $statusCode   = 500;
    protected string $errorType = 'CATALOG_COMPILATION_ERROR';

    public static function default(?Throwable $e): self
    {
        return new self(
            "Une erreur est survenue lors de la compilation du catalogue. Veuillez réessayer plus tard ou contacter le support si le problème persiste.",
            previous: $e
        );
    }

}
