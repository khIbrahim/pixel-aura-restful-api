<?php

namespace App\DTO\V1\Order;

final class OrderPricing
{

    public function __construct(
        public int $subtotal_cents,
        public int $tax_cents,
        public int $total_cents,
        public int $discount_cents = 0, // TODO
        public int $delivery_fee_cents = 0, // TODO
    ){}

}
