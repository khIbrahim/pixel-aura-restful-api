<?php

namespace App\Exceptions\V1\Item;

use App\Exceptions\V1\BaseApiException;
use Illuminate\Database\QueryException;

class ItemDeletionException extends BaseApiException
{
    public static function queryError(QueryException $e): self
    {
        return new self(
            "Erreur lors de la suppression de l'item: ".$e->getMessage(),
            500
        );
    }

    public static function notFound(): self
    {
        return new self(
            "Item non trouvé ou n'appartient pas à ce magasin",
            404
        );
    }

    public static function unauthorized(): self
    {
        return new self(
            "Vous n'êtes pas autorisé à supprimer cet item",
            403
        );
    }

    public static function hasVariants(): self
    {
        return new self(
            'Impossible de supprimer un item qui possède des variantes actives',
            422
        );
    }
}
