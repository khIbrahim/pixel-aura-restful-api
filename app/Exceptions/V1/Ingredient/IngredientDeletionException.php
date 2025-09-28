<?php

namespace App\Exceptions\V1\Ingredient;

use App\Exceptions\V1\BaseApiException;

class IngredientDeletionException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'ingredient_deletion_failed';

    public static function usedInActiveItems(): self
    {
        return new self("Impossible de supprimer un ingrédient utilisé dans des articles actifs");
    }

    public static function isMandatoryAllergen(): self
    {
        return new self("Impossible de supprimer un allergène obligatoire");
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la suppression de l'ingrédient: {$reason}" : "Erreur lors de la suppression de l'ingrédient";
        return new self($message);
    }
}
