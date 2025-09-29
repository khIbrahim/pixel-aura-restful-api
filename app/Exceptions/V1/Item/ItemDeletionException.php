<?php

namespace App\Exceptions\V1\Item;

use App\Exceptions\V1\BaseApiException;
use Illuminate\Database\QueryException;
use Throwable;

class ItemDeletionException extends BaseApiException
{
    public static function queryError(QueryException $e): self
    {
        return new self(
            "Erreur lors de la suppression de l'item: ".$e->getMessage(),
            500
        );
    }

    public static function hasVariants(): self
    {
        return new self(
            'Impossible de supprimer un item qui possède des variantes actives',
            422
        );
    }

    public static function default(?Throwable $e): self
    {
        return new self(
            "Erreur lors de la suppression de l'item.",
            500,
            previous: $e
        );
    }

}
