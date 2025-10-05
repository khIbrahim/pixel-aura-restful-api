<?php

namespace App\Services\V1\Order;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\Models\V1\Order;
use App\Models\V1\OrderItem;

class OrderPreparationService
{

    public function __construct(
        protected array $config,
        private readonly OrderRepositoryInterface $orderRepository
    ){}

    public function calculatePreparationTime(Order $order): int
    {
        $baseTime = $order->items->sum(fn(OrderItem $item) => $item->getPreparationTime() * $item->quantity);
        if($baseTime === 0){
            $baseTime = (int) $this->config['default_minutes'];
        }

        $prepTime = $this->applyRushHourAdjustment($baseTime, $order);
        $prepTime = $this->applyBulkOrderAdjustment($prepTime, $order);

        return $this->applyKitchenLoadAdjustment($prepTime, $order);
    }

    protected function applyRushHourAdjustment(int $baseTime, Order $order): int
    {
        $config = $this->config['rush_hour'] ?? [];
        if(! $config['enabled']){
            return $baseTime;
        }

        $startHour = $config['start'] ?? '00:00';
        $endHour   = $config['end'] ?? '23:59';
        $days      = $config['days'] ?? [1, 2, 3, 4, 5];

        $orderTime = $order->confirmed_at ?? $order->created_at ?? now();
        $orderDay  = $orderTime->day;
        $orderHour = $orderTime->format('H:i');

        if(in_array($orderDay, $days) && $orderHour >= $startHour && $orderHour <= $endHour){
            return (int) ceil($baseTime * (float) ($config['extra_multiplier'] ?? 1));
        }

        return $baseTime;
    }

    protected function applyBulkOrderAdjustment(int $prepTime, Order $order): int
    {
        $config = $this->config['bulk_order'] ?? [];
        if(! ($config['enabled'] ?? false)){
            return $prepTime;
        }

        $itemCount = $order->items->sum('quantity');
        if ($itemCount < ((int) $config['threshold'] ?? 0)) {
            return $prepTime;
        }

        $extraMinutes = (int) ($config['extra_minutes'] ?? 0);
        return $prepTime + $extraMinutes;
    }

    protected function applyKitchenLoadAdjustment(int $prepTime, Order $order): int
    {
        $config = $this->config['kitchen_load'] ?? [];
        if(! ($config['enabled'] ?? false)){
            return $prepTime;
        }

        $checkInterval  = (int) ($config['check_interval'] ?? 0);
        $highLoadOrders = (int) ($config['high_load_orders'] ?? 0);
        $extraMinutes   = (int) ($config['extra_minutes'] ?? 0);

        $activeOrderCount = $this->orderRepository->countActiveOrdersInLastMinutes($order->store_id, $checkInterval);

        if($activeOrderCount < $highLoadOrders){
            return $prepTime;
        }

        return $prepTime + $extraMinutes;
    }

}
