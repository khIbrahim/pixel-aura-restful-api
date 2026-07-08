<?php

namespace App\Services\V1\Order;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\Contracts\V1\Order\OrderServiceInterface;
use App\DTO\V1\Order\OrderData;
use App\Enum\V1\Order\OrderStatus;
use App\Events\V1\Order\OrderCreated;
use App\Events\V1\Order\OrderStatusChanged;
use App\Exceptions\V1\Order\OrderCreationException;
use App\Exceptions\V1\Order\OrderUpdateException;
use App\Models\V1\Order;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class OrderService implements OrderServiceInterface
{

    public function __construct(
        private OrderDataEnricherService    $enricher,
        private OrderValidationService      $validationService,
        private OrderPriceCalculatorService $calculatorService,
        private OrderRepositoryInterface    $repository,
        private OrderNumberService          $numberService,
        private OrderPreparationService     $preparationService
    ){}

    /**
     * @throws OrderCreationException
     */
    public function create(OrderData $data): Order
    {
        try {
            Log::info("Création de la commande", ['data' => $data->toArray()]);

            $order = DB::transaction(function () use ($data) {
                $data = $this->enricher->enrich($data);
                $this->validationService->validate($data);
                $pricedData         = $this->calculatorService->calculate($data);
                $pricedData->number = $this->numberService->generate($pricedData->store_id);

                foreach($data->items as $orderItem){
                    $item = $orderItem->item;
                    if($item->track_inventory){
                        $stock = $item->stock - $orderItem->quantity;
                        if($stock < 0){
                            throw OrderCreationException::insufficientStock($item->id, $item->name);
                        }

                        $item->update(['stock' => $stock]);
                    }
                }

                $order = $this->repository->createOrder($pricedData);

                Log::info("Commande créée avec succès", [
                    'order_id'     => $order->id,
                    'order_number' => $order->number,
                    'store_id'     => $order->store_id,
                ]);

                return $order;
            });

            broadcast(new OrderCreated($order))->toOthers();
            return $order;
        } catch(Throwable $e){
            Log::error("Une erreur est survenue lors de la création de la commande : " . $e->getMessage(), [
                'exception' => $e,
            ]);

            if($e instanceof OrderCreationException){
                throw $e;
            }

            throw OrderCreationException::default($e);
        }
    }

    /**
     * @throws OrderUpdateException
     */
    public function updateStatus(Order $order, OrderStatus|string $newStatus): Order
    {
        if(is_string($newStatus) && ! OrderStatus::tryFrom($newStatus)){
            throw OrderUpdateException::invalidStatus($newStatus);
        }

        if($newStatus instanceof OrderStatus){
            $newStatus = $newStatus->value;
        }

        [$order, $oldStatus] = DB::transaction(function () use ($order, $newStatus){
            $oldStatus = $order->status;
            $timestamp = match($newStatus) {
                OrderStatus::Confirmed->value  => 'confirmed_at',
                OrderStatus::Ready->value      => 'ready_at',
                OrderStatus::Preparing->value  => 'preparing_at',
                OrderStatus::Completed->value  => 'completed_at',
                OrderStatus::Cancelled->value  => 'cancelled_at',
            };

            /** @var Order $order */
            $order = $this->repository->update($order, [
                'status'   => $newStatus,
                $timestamp => now()
            ]);

            return [$order, $oldStatus];
        });

        broadcast(new OrderStatusChanged(
            order:           $order,
            oldStatus:       $oldStatus,
            newStatus:       OrderStatus::from($newStatus),
            preparationData: $order->getPreparationData()
        ))->toOthers();

        return $order;
    }

    public function updateEstimatedTime(Order $order): Order
    {
        $prepTime = $this->preparationService->calculatePreparationTime($order);

        /** @var Order */
        return $this->repository->update($order, [
            'estimated_preparation_minutes' => $prepTime,
            'excepted_ready_at'             => now()->addMinutes($prepTime)
        ]);
    }

    public function listForDashboard(int $storeId, array $filters, int $perPage = 25, string $timezone = 'UTC'): LengthAwarePaginator
    {
        if(! empty($filters['date_from'])){
            $filters['created_from'] = CarbonImmutable::parse($filters['date_from'], $timezone)->startOfDay()->utc();
        }

        if(! empty($filters['date_to'])){
            $filters['created_to'] = CarbonImmutable::parse($filters['date_to'], $timezone)->endOfDay()->utc();
        }

        unset($filters['date_from'], $filters['date_to']);

        return $this->repository->paginateForDashboard(
            storeId: $storeId,
            filters: $filters,
            perPage: $perPage
        );
    }
}
