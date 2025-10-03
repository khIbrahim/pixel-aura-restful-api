<?php

namespace App\DTO\V1\Order;

final class ItemPricing
{

    public function __construct(
        public int $base_price_cents,
        public int $options_price_cents,
        public int $modifications_price_cents,
        public int $item_total_cents,
        public int $final_total_cents,
    ){}

}
