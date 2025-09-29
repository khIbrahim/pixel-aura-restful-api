<?php

namespace App\Exceptions\V1\Item;

use App\Exceptions\V1\BaseApiException;
use Illuminate\Database\QueryException;
use Throwable;

class ItemCreationException extends BaseApiException
{
    protected $code             = 400;
    protected string $errorType = "ITEM_CREATION_ERROR";

    public static function queryError(QueryException $e): self
    {
        $message = match(true) {
            str_contains($e->getMessage(), 'Duplicate entry')        => "Un item avec ces informations existe déjà.",
            str_contains($e->getMessage(), 'foreign key constraint') => "Une des relations n'est pas valide.",
            default                                                  => "Erreur lors de la création de l'item."
        };

        return new static($message, previous: $e);
    }

    public static function default(?Throwable $e): self
    {
        return new self(
            "Erreur lors de la création de l'item.",
            previous: $e
        );
    }

}
