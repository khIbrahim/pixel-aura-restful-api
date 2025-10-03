<?php

namespace App\Enum\V1\Order;

enum OrderServiceType: string
{

    case DineIn    = 'dine_in'; // Sur place
    case Delivery  = 'delivery'; // Livraison
    case Pickup    = 'pickup'; // Cueillette

    public function isDelivery(): bool
    {
        return $this === self::Delivery;
    }
}
