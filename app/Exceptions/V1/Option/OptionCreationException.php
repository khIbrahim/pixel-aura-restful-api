<?php

namespace App\Exceptions\V1\Option;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class OptionCreationException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'OPTION_CREATION_ERROR';

    public static function nameAlreadyExists(string $name): self
    {
        return new self("L'option '$name' existe déjà")->addContext('name', $name);
    }

    public static function default(?Throwable $e): self
    {
        return new self(
            "Une erreur est survenue lors de la création de l'option",
            previous: $e
        );
    }
}
