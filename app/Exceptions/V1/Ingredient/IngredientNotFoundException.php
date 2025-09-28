<?php

namespace App\Exceptions\V1\Ingredient;

use App\Exceptions\V1\BaseApiException;

class IngredientNotFoundException extends BaseApiException
{
    protected int $statusCode = 404;
    protected string $errorType = 'ingredient_not_found';

    public static function withId(int $id): self
    {
        return new self("Ingrédient avec l'ID $id non trouvé")->addContext('ingredient_id', $id);
    }

    public static function withName(string $name): self
    {
        return new self("Ingrédient '$name' non trouvé")->addContext('name', $name);
    }

    public static function default(): self
    {
        return new self('Ingrédient non trouvé');
    }
}
