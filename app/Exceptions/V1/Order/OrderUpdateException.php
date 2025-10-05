<?php

namespace App\Exceptions\V1\Order;

use App\Exceptions\V1\BaseApiException;

class OrderUpdateException extends BaseApiException
{

    protected int $statusCode   = 500;
    protected string $errorType = 'ORDER_UPDATE_ERROR';

    public static function invalidStatus(string $status): self
    {
        return new self("Le status '$status' n'est pas valide pour une commande.");
    }

}
