<?php

namespace App\Exceptions\V1\Item;

use App\Exceptions\V1\BaseApiException;
use Illuminate\Database\QueryException;
use Throwable;

class ItemUpdateException extends BaseApiException
{

    protected $code             = 500;
    protected string $errorType = "ITEM_UPDATE_ERROR";

    public static function queryError(QueryException $e): self
    {
        return new self(
            "Erreur lors de la mise à jour de l'item: ".$e->getMessage(),
            500
        );
    }

    public static function default(?Throwable $e): self
    {
        return new self(
            "Une erreur est survenue lors de la mise à jour de l'item".($e ? ": ".$e->getMessage() : ""),
            previous: $e
        );
    }

}
