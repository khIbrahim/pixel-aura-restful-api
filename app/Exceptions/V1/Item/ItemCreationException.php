<?php

namespace App\Exceptions\V1\Item;

class ItemCreationException extends \Exception{

    public static function queryError(\Throwable $e): self
    {
        return new self("Une erreur est survenue lors de la création de l'item : " . $e->getMessage(), previous: $e);
    }
}
