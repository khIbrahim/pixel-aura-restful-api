<?php

namespace App\Exceptions\V1\Category;

use App\Exceptions\V1\BaseApiException;

class CategoryDeletionException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'CATEGORY_DELETION_ERROR';

    public static function hasItems(): self
    {
        return new self("Impossible de supprimer une catégorie contenant des articles");
    }

    public static function hasSubcategories(): self
    {
        return new self("Impossible de supprimer une catégorie contenant des sous-catégories");
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la suppression de la catégorie: $reason" : "Erreur lors de la suppression de la catégorie";
        return new self($message);
    }
}
