<?php

namespace App\Repositories\V1\Dashboard;

use App\Contracts\V1\Dashboard\DashboardStatsRepositoryInterface;
use App\Enum\V1\Order\OrderStatus;
use App\Models\V1\Item;
use App\Models\V1\Order;
use App\Models\V1\OrderItem;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DashboardStatsRepository implements DashboardStatsRepositoryInterface
{

    public function countOrders(int $storeId, CarbonInterface $start, CarbonInterface $end): int
    {
        return Order::query()
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->count();
    }

    public function sumRevenueCents(int $storeId, CarbonInterface $start, CarbonInterface $end): int
    {
        return (int) Order::query()
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::Completed->value)
            ->sum('total_cents');
    }

    public function countActiveItems(int $storeId): int
    {
        return Item::query()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->count();
    }

    public function getOrdersRevenueBetween(int $storeId, CarbonInterface $start, CarbonInterface $end): Collection
    {
        return Order::query()
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->get(['created_at', 'total_cents']);
    }

    public function getTopItems(int $storeId, CarbonInterface $start, CarbonInterface $end, int $limit = 5): Collection
    {
        return OrderItem::query()
            ->selectRaw('item_id, item_name as name, SUM(quantity) as quantity_sold, SUM(final_total_cents) as revenue_cents')
            ->whereHas('order', function($query) use ($storeId, $start, $end) {
                $query->where('store_id', $storeId)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('status', '!=', OrderStatus::Cancelled->value);
            })
            ->groupBy('item_id', 'item_name')
            ->orderByDesc('quantity_sold')
            ->limit($limit)
            ->get();
    }
}
