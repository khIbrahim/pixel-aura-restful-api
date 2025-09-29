<?php

namespace App\Exceptions\V1\Ingredient;

use App\Exceptions\V1\BaseApiException;

class IngredientUpdateException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'INGREDIENT_UPDATE_ERROR';

    public static function nameAlreadyExists(string $name): self
    {
        return new self("L'ingrédient '$name' existe déjà")->addContext('name', $name);
    }

    public static function cannotChangeAllergenStatus(): self
    {
        return new self("Impossible de modifier le statut allergène - ingrédient utilisé dans des articles actifs");
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la mise à jour de l'ingrédient: {$reason}" : "Erreur lors de la mise à jour de l'ingrédient";
        return new self($message);
    }
}
