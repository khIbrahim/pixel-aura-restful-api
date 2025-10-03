<?php

namespace App\Enum\V1\Order;

enum OrderChannel: string
{

    case Device = 'device';
    case Api    = 'api';
    case Pos    = 'pos';
    case Web    = 'web';
    case Mobile = 'mobile';

}
