<?php

namespace App\Exceptions\V1\Option;

use App\Exceptions\V1\BaseApiException;

class OptionCreationException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'option_creation_failed';

    public static function nameAlreadyExists(string $name): self
    {
        return new self("L'option '$name' existe déjà")->addContext('name', $name);
    }

    public static function invalidPriceRange(): self
    {
        return new self("Le prix de l'option est invalide");
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la création de l'option: $reason" : "Erreur lors de la création de l'option";
        return new self($message);
    }
}
