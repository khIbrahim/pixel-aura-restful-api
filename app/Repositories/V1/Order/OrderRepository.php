<?php

namespace App\Repositories\V1\Order;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\DTO\V1\Order\OrderData;
use App\Enum\V1\Order\OrderStatus;
use App\Models\V1\Order;
use App\Repositories\V1\BaseRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{

    public function createOrder(OrderData $data): Order
    {
        return DB::transaction(function() use ($data){
            $payload   = collect($data->toArray(false));
            $itemsData = $payload->pull('items', []);

            /** @var Order $order */
            $order = $this->create($payload->toArray());

            if(! empty($itemsData)){
                $order->items()->createMany($itemsData);
            }

            $order->load('items.item');

            return $order;
        });
    }

    public function model(): string
    {
        return Order::class;
    }

    public function countActiveOrdersInLastMinutes(int $storeId, ?int $minutes = 10): int
    {
        $query = $this->query()
            ->where('store_id', $storeId)
            ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Preparing]);

        if($minutes !== null && $minutes > 0){
            $query
                ->whereTime('created_at', '>=', now()->subMinutes($minutes))
                ->orWhereTime('confirmed_at', '>=', now()->subMinutes($minutes));
        }

        return $query->count();
    }

    public function getOrdersByStatuses(array $statuses): Collection
    {
        return $this->query()
            ->whereIn('status', $statuses)
            ->where(function($query){
                $query->whereNotNull('confirmed_at')
                      ->whereNotNull('preparing_at');
            })
            ->get();
    }
}
