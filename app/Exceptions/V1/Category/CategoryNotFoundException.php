<?php

namespace App\Exceptions\V1\Category;

use App\Exceptions\V1\BaseApiException;

class CategoryNotFoundException extends BaseApiException
{
    protected int $statusCode   = 404;
    protected string $errorType = 'category_not_found';

    public static function withId(int $id): self
    {
        return new self("Catégorie avec l'ID $id non trouvée")->addContext('category_id', $id);
    }

    public static function withSlug(string $slug): self
    {
        return new self("Catégorie avec le slug '$slug' non trouvée")->addContext('slug', $slug);
    }

    public static function default(): self
    {
        return new self('Catégorie non trouvée');
    }
}
