<?php

namespace App\Services\V1\Order;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\Contracts\V1\Order\OrderServiceInterface;
use App\DTO\V1\Order\OrderData;
use App\Events\V1\Order\OrderCreated;
use App\Exceptions\V1\Order\OrderCreationException;
use App\Models\V1\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class OrderService implements OrderServiceInterface
{

    public function __construct(
        private OrderDataEnricherService    $enricher,
        private OrderValidationService      $validationService,
        private OrderPriceCalculatorService $calculatorService,
        private OrderRepositoryInterface    $orderRepository,
        private OrderNumberService          $orderNumberService
    ){}

    /**
     * @throws OrderCreationException
     */
    public function create(OrderData $data): Order
    {
        try {
            Log::info("Création de la commande", ['data' => $data->toArray()]);

            return DB::transaction(function () use ($data) {
                $data = $this->enricher->enrich($data);
                $this->validationService->validate($data);
                $pricedData         = $this->calculatorService->calculate($data);
                $pricedData->number = $this->orderNumberService->generate($pricedData->store_id);

                $order = $this->orderRepository->createOrder($pricedData);

                Log::info("Commande créée avec succès", [
                    'order_id'     => $order->id,
                    'order_number' => $order->number,
                    'store_id'     => $order->store_id,
                ]);

                broadcast(new OrderCreated($order))->toOthers();

                return $order;
            });
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

}
