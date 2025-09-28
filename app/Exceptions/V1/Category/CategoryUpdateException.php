<?php

namespace App\Exceptions\V1\Category;

use App\Exceptions\V1\BaseApiException;

class CategoryUpdateException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'category_update_failed';

    public static function cannotUpdateParent(): self
    {
        return new self("Impossible de modifier la catégorie parente - cycle détecté");
    }

    public static function hasActiveItems(): self
    {
        return new self("Impossible de désactiver une catégorie contenant des articles actifs");
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la mise à jour de la catégorie: $reason" : "Erreur lors de la mise à jour de la catégorie";
        return new self($message);
    }
}
