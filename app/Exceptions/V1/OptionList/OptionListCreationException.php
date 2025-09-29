<?php

namespace App\Exceptions\V1\OptionList;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class OptionListCreationException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'OPTION_LIST_CREATION_ERROR';

    public static function nameAlreadyExists(string $name): self
    {
        return new self("La liste d'options '$name' existe déjà")->addContext('name', $name);
    }

    public static function invalidSelectionLimits(int $min, int $max): self
    {
        return new self("Limites de sélection invalides: min=$min, max=$max")
            ->addContext('min_selections', $min)
            ->addContext('max_selections', $max);
    }

    public static function default(?Throwable $e): self
    {
        return new self(
            "Une erreur est survenue lors de la création de la liste d'options",
            previous: $e
        );
    }
}
