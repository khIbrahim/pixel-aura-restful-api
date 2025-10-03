<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Order\OrderServiceInterface;
use App\Exceptions\V1\Order\OrderCreationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Order\CreateOrderRequest;
use App\Http\Resources\V1\Order\OrderResource;
use App\Hydrators\V1\Order\OrderHydrator;
use App\Models\V1\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{

    public function __construct(
        private readonly OrderServiceInterface $service,
        private readonly OrderHydrator         $hydrator
    ){}

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

}
