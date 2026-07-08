<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Order\OrderServiceInterface;
use App\Contracts\V1\Order\OrderStatusServiceInterface;
use App\Exceptions\V1\Order\InvalidOrderStatusTransitionException;
use App\Exceptions\V1\Order\OrderCreationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Order\CreateOrderRequest;
use App\Http\Requests\V1\Order\ListOrdersRequest;
use App\Http\Requests\V1\Order\UpdateOrderStatusRequest;
use App\Http\Resources\V1\Order\OrderResource;
use App\Hydrators\V1\Order\OrderHydrator;
use App\Models\V1\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{

    public function __construct(
        private readonly OrderServiceInterface       $service,
        private readonly OrderHydrator               $hydrator,
        private readonly OrderStatusServiceInterface $statusService,
    ){}

    public function index(ListOrdersRequest $request): JsonResponse
    {
        $store     = $request->attributes->get('store');
        $validated = $request->validated();
        $perPage   = (int) ($validated['per_page'] ?? 25);

        unset($validated['page'], $validated['per_page']);

        $orders = $this->service->listForDashboard(
            storeId:  (int) $store->id,
            filters:  $validated,
            perPage:  $perPage,
            timezone: $store->timezone ?? config('app.timezone')
        );

        return response()->json([
            'data' => OrderResource::collection($orders->getCollection()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
                'last_page'    => $orders->lastPage(),
            ]
        ]);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->service->create($this->hydrator->fromCreateRequest($request));

            return response()->json([
                'message' => "La commande a été créée avec succès.",
                'data'    => new OrderResource($order->load('items'))
            ], 201);
        } catch(OrderCreationException $e){
            return response()->json([
                'message'  => $e->getMessage(),
                'error'    => $e->getErrorType(),
                'context'  => $e->getContext(),
                'previous' => $e->getPrevious()?->getMessage() ?? null
            ], $e->getStatusCode());
        }
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'data' => new OrderResource($order->load('items', 'creator', 'device', 'store'))
        ]);
    }

    public function getPreparationStatus(Order $order): JsonResponse
    {
        return response()->json([
            'data' => $order->getPreparationData()
        ]);
    }

    public function updateStatus(Order $order, UpdateOrderStatusRequest $request): JsonResponse
    {
        try {
            $storeId = (int) $request->attributes->get('store')->id;

            $updatedOrder = $this->statusService->transition(
                storeId:  $storeId,
                order:    $order,
                newStatus: $request->status()
            );

            return response()->json([
                'message' => 'Le statut de la commande a été mis à jour.',
                'data'    => new OrderResource($updatedOrder)
            ]);
        } catch(InvalidOrderStatusTransitionException $e){
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'invalid_order_status_transition',
                'context' => [
                    'old_status' => $e->oldStatus->value,
                    'new_status' => $e->newStatus->value,
                ]
            ], 422);
        } catch(Throwable $e){
            Log::error(
                "Une erreur s'est produite lors de la modification du statut de la commande.",
                [
                    'order_id'   => $order->id,
                    'new_status' => $request->validated('status'),
                    'message'    => $e->getMessage(),
                    'exception'  => $e::class,
                ]
            );

            return response()->json([
                'message' => "Une erreur s'est produite."
            ], 500);
        }
    }

}
