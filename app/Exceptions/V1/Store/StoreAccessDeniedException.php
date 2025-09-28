<?php

namespace App\Exceptions\V1\Store;

use App\Exceptions\V1\BaseApiException;

class StoreAccessDeniedException extends BaseApiException
{
    protected int $statusCode = 403;
    protected string $errorType = 'store_access_denied';

    public static function forUser(int $userId, int $storeId): self
    {
        return new self("Accès refusé au magasin")
            ->addContext('user_id', $userId)
            ->addContext('store_id', $storeId);
    }

    public static function default(): self
    {
        return new self('Accès refusé au magasin');
    }
}
