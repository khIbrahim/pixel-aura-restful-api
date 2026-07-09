<?php

namespace App\Services\V1\Notification;

use App\Enum\V1\Notification\NotificationSeverity;
use App\Models\V1\Notification;
use App\Models\V1\Order;

class OrderNotificationService
{

    public function created(Order $order, ?string $eventId): Notification
    {
        return Notification::query()->firstOrCreate([
            'event_id' => $eventId
        ], [
            'store_id'       => $order->store_id,
            'event_id'       => $eventId,
            'title'          => "Nouvelle commande",
            'type'           => 'order.created',
            'message'        => "La commande #$order->number vient d'être reçue",
            'severity'       => NotificationSeverity::Success,
            'subject_type'   => Order::class,
            'subject_id'     => $order->id,
            'subject_number' => $order->number,
            'action_url'     => '/orders',
            'daata'          => [
                'order_id'     => $order->id,
                'order_number' => $order->number,
                'status'       => $order->status->value,
                'total_cents'  => $order->total_cents,
                'currency'     => $order->currency,
            ]
        ]);
    }

}
