<?php

namespace App\Enum\V1\Discount;

enum BuyXGetYType: string
{

    case Cheapest = 'cheapest';
    case SameItem = 'same_item';
    case AnyItem  = 'any_item';
}
