<?php

namespace App\Exceptions\V1\Category;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class CategoryCreationException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'CATEGORY_CREATION_ERROR';

    public static function skuAlreadyExists(string $sku): self
    {
        return new self("Le sku '$sku' existe déjà")->addContext('sku', $sku);
    }

    public static function positionDuplicate(int $position): self
    {
        return new self("La position $position est déjà utilisée")->addContext('position', $position);
    }

    public static function invalidParent(int $parentId): self
    {
        return new self("Catégorie parente invalide")
            ->addContext('parent_id', $parentId);
    }

    public static function default(?Throwable $e): self
    {
        return new self(
            "Erreur lors de la création de la catégorie.",
            previous: $e
        );
    }
}
