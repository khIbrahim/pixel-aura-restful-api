<?php

namespace App\Contracts\V1\Printing;

use App\Models\V1\Order;
use App\Models\V1\StorePrintSettings;

interface PrintingServiceInterface
{

    public function printers(): array;

    public function settings(int $storeId): StorePrintSettings;

    public function updateSettings(int $storeId, array $payload): StorePrintSettings;

    public function printOrder(Order $order, array $types): array;

    public function printOrderCreatedTickets(Order $order): void;

    public function printKitchenTicket(Order $order): void;

}
