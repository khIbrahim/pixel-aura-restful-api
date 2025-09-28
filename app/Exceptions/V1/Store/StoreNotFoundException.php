<?php

namespace App\Exceptions\V1\Store;

use App\Exceptions\V1\BaseApiException;

class StoreNotFoundException extends BaseApiException
{
    protected int $statusCode = 404;
    protected string $errorType = 'store_not_found';

    public static function withId(int $id): self
    {
        return new self("Magasin avec l'ID {$id} non trouvé")->addContext('store_id', $id);
    }

    public static function default(): self
    {
        return new self('Magasin non trouvé');
    }
}
