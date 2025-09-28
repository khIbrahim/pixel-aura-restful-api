<?php

namespace App\Exceptions\V1\Ingredient;

use App\Exceptions\V1\BaseApiException;

class IngredientCreationException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'ingredient_creation_failed';

    public static function nameAlreadyExists(string $name): self
    {
        return new self("L'ingrédient '$name' existe déjà")->addContext('name', $name);
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la création de l'ingrédient: {$reason}" : "Erreur lors de la création de l'ingrédient";
        return new self($message);
    }
}
