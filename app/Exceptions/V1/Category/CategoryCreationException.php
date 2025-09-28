<?php

namespace App\Exceptions\V1\Category;

use App\Exceptions\V1\BaseApiException;

class CategoryCreationException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'category_creation_failed';

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

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la création de la catégorie: $reason" : "Erreur lors de la création de la catégorie";
        return new self($message);
    }
}
