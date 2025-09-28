<?php

namespace App\Exceptions\V1\OptionList;

use App\Exceptions\V1\BaseApiException;

class OptionListCreationException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'option_list_creation_failed';

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

    public static function emptyOptionsList(): self
    {
        return new self("Impossible de créer une liste d'options vide");
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la création de la liste d'options: $reason" : "Erreur lors de la création de la liste d'options";
        return new self($message);
    }
}
