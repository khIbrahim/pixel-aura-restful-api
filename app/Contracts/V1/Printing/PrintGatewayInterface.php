<?php

namespace App\Contracts\V1\Printing;

use App\Models\V1\Order;

interface PrintGatewayInterface
{

    public function printers(): array;

    public function print(Order $order, array $printConfig): array;

}
