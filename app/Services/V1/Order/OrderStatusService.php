<?php

namespace App\Services\V1\Order;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\Contracts\V1\Order\OrderStatusServiceInterface;
use App\Enum\V1\Order\OrderStatus;
use App\Events\V1\Order\OrderStatusChanged;
use App\Exceptions\V1\Order\InvalidOrderStatusTransitionException;
use App\Models\V1\Item;
use App\Models\V1\Order;
use Illuminate\Support\Facades\DB;

class OrderStatusService implements OrderStatusServiceInterface
{

    private const array ALLOWED_TRANSITIONS = [
        OrderStatus::Confirmed->value => [
            OrderStatus::Preparing,
            OrderStatus::Cancelled,
        ],
        OrderStatus::Preparing->value => [
            OrderStatus::Ready,
            OrderStatus::Cancelled,
        ],
        OrderStatus::Ready->value     => [
            OrderStatus::Completed,
            OrderStatus::Cancelled,
        ],
        OrderStatus::Completed->value => [],
        OrderStatus::Cancelled->value => [],
    ];

    public function __construct(
        private readonly OrderRepositoryInterface $repository,
        private readonly OrderPreparationService  $preparationService
    ){}

    public function transition(int $storeId, Order $order, OrderStatus $newStatus): Order
    {
        [$updatedOrder, $oldStatus, $hasChanged] = DB::transaction(function() use ($storeId, $order, $newStatus){
            /** @var Order $lockedOrder */
           $lockedOrder = $this->repository
                ->query()
               ->where('store_id', $storeId)
               ->whereKey($order->id)
               ->with('items.item')
               ->lockForUpdate()
               ->firstOrFail();

           $oldStatus = $lockedOrder->status;
           if($oldStatus === $newStatus){
               return [
                   $lockedOrder->load('items.item', 'creator', 'device', 'store'),
                   $oldStatus,
                   false
               ];
           }

            $this->ensureTransitionIsAllowed(
                oldStatus: $oldStatus,
                newStatus: $newStatus
            );

            $payload = [
                'status'                              => $newStatus->value,
                $this->getTimestampColumn($newStatus) => now(),
            ];

            if($newStatus === OrderStatus::Preparing){
                $preparationTime = $this->preparationService
                    ->calculatePreparationTime($lockedOrder);

                $payload['estimated_preparation_minutes'] = $preparationTime;
                $payload['excepted_ready_at']             = now()->addMinutes($preparationTime);
            }

            if($newStatus === OrderStatus::Ready){
                $payload['excepted_ready_at'] = now();
            }

            if($newStatus === OrderStatus::Cancelled){
                $payload['excepted_ready_at'] = null;

                if($oldStatus === OrderStatus::Confirmed){
                    $this->restoreStock($lockedOrder);
                }
            }

            /** @var Order $updatedOrder */
            $updatedOrder = $this->repository->update($lockedOrder, $payload);

            $updatedOrder->load(
                'items.item',
                'creator',
                'device',
                'store'
            );

            return [
                $updatedOrder,
                $oldStatus,
                true
            ];
        });

        if($hasChanged){
            broadcast(new OrderStatusChanged(
                order:           $updatedOrder,
                oldStatus:       $oldStatus,
                newStatus:       $newStatus,
                preparationData: $updatedOrder->getPreparationData()
            ))->toOthers();
        }

        return $updatedOrder;
    }

    private function ensureTransitionIsAllowed(
        OrderStatus $oldStatus,
        OrderStatus $newStatus
    ): void {
        $allowedStatuses = self::ALLOWED_TRANSITIONS[$oldStatus->value] ?? [];

        if(! in_array($newStatus, $allowedStatuses, true)){
            throw new InvalidOrderStatusTransitionException(
                oldStatus: $oldStatus,
                newStatus: $newStatus
            );
        }
    }

    private function getTimestampColumn(OrderStatus $status): string
    {
        return match($status){
            OrderStatus::Preparing => 'preparing_at',
            OrderStatus::Ready     => 'ready_at',
            OrderStatus::Completed => 'completed_at',
            OrderStatus::Cancelled => 'cancelled_at',

            default => throw new InvalidOrderStatusTransitionException(
                oldStatus: $status,
                newStatus: $status
            )
        };
    }

    private function restoreStock(Order $order): void
    {
        foreach($order->items as $orderItem){
            if(! $orderItem->item?->track_inventory){
                continue;
            }

            /** @var Item $item */
            $item = Item::query()
                ->whereKey($orderItem->item->id)
                ->lockForUpdate()
                ->first();

            if(! $item){
                continue;
            }

            $item->increment('stock', $orderItem->quantity);
        }
    }

}
