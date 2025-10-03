<?php

namespace App\Repositories\V1\Order;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\DTO\V1\Order\OrderData;
use App\Models\V1\Order;
use App\Repositories\V1\BaseRepository;
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
}
