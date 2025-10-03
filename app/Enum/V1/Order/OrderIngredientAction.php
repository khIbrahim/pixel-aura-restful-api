<?php

namespace App\Enum\V1\Order;

enum OrderIngredientAction: string
{

    case Add    = 'add';
    case Remove = 'remove';

}
