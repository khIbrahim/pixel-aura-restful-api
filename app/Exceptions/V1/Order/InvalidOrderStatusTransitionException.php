<?php

namespace App\Exceptions\V1\Order;

use App\Enum\V1\Order\OrderStatus;
use RuntimeException;

class InvalidOrderStatusTransitionException extends RuntimeException
{

    public function __construct(
        public readonly OrderStatus $oldStatus,
        public readonly OrderStatus $newStatus,
    )
    {
        parent::__construct("La commande ne peut pas passer du statut $oldStatus->value au statut $newStatus->value");
    }

}
