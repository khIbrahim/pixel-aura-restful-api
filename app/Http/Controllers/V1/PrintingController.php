<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Printing\PrintingServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Printing\PrintOrderRequest;
use App\Http\Requests\V1\Printing\UpdatePrintSettingsRequest;
use App\Http\Resources\V1\Printing\PrintSettingsResource;
use App\Models\V1\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PrintingController extends Controller
{

    public function __construct(
        private readonly PrintingServiceInterface $printingService
    ){}

    public function printers(): JsonResponse
    {
        return response()->json([
            'data' => $this->printingService->printers()
        ]);
    }

    public function settings(Request $request): JsonResponse
    {
        $store = $request->attributes->get('store');

        return response()->json([
            'data' => new PrintSettingsResource(
                $this->printingService->settings((int) $store->id)
            )
        ]);
    }

    public function updateSettings(UpdatePrintSettingsRequest $request): JsonResponse
    {
        $store = $request->attributes->get('store');

        return response()->json([
            'message' => "Les réglages d'impression ont été mis à jour.",
            'data'    => new PrintSettingsResource(
                $this->printingService->updateSettings(
                    storeId: (int) $store->id,
                    payload: $request->validated()
                )
            )
        ]);
    }

    public function printOrder(Order $order, PrintOrderRequest $request): JsonResponse
    {
        $store = $request->attributes->get('store');

        abort_if((int) $order->store_id !== (int) $store->id, 404);

        try {
            return response()->json([
                'message' => "La demande d'impression a été envoyée.",
                'data'    => $this->printingService->printOrder(
                    order: $order,
                    types: $request->validated('types')
                )
            ], 202);
        } catch(Throwable $e){
            Log::error("Une erreur est survenue lors de l'impression de la commande.", [
                'order_id'  => $order->id,
                'store_id'  => $store->id,
                'types'     => $request->validated('types'),
                'message'   => $e->getMessage(),
                'exception' => $e::class,
            ]);

            return response()->json([
                'message' => "Le service d'impression est indisponible ou mal configuré."
            ], 502);
        }
    }

}
