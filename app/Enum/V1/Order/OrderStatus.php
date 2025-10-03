<?php

namespace App\Enum\V1\Order;

enum OrderStatus: string
{

    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready     = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

}
