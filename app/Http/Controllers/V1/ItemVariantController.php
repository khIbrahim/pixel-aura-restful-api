<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\ItemVariant\ItemVariantServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ItemVariant\CreateItemVariantRequest;
use App\Http\Requests\V1\ItemVariant\UpdateItemVariantRequest;
use App\Http\Resources\V1\ItemVariantResource;
use App\Hydrators\V1\ItemVariant\ItemVariantHydrator;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use Illuminate\Http\JsonResponse;
use Throwable;

class ItemVariantController extends Controller
{
    public function __construct(
        private readonly ItemVariantServiceInterface $service,
        private readonly ItemVariantHydrator $hydrator
    ) {}

    public function index(Item $item): JsonResponse
    {
        $variants = $this->service->getVariantsByItem($item);

        return response()->json([
            'data' => ItemVariantResource::collection($variants),
            'meta' => [
                'total' => $variants->count(),
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                ],
            ],
        ]);
    }

    public function store(CreateItemVariantRequest $request, Item $item): JsonResponse
    {
        try {
            $data = $this->hydrator->fromCreateRequest($request, $item);
            $variant = $this->service->createVariant($item, $data);

            return response()->json([
                'message' => 'Variante créée avec succès',
                'data' => new ItemVariantResource($variant),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Une erreur s'est produite lors de la création de la variante",
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function show(Item $item, ItemVariant $itemVariant): JsonResponse
    {
        return response()->json([
            'data' => new ItemVariantResource($itemVariant),
        ]);
    }

    public function update(UpdateItemVariantRequest $request, Item $item, ItemVariant $itemVariant): JsonResponse
    {
        try {
            $data = $this->hydrator->fromUpdateRequest($request, $item, $itemVariant);
            $variant = $this->service->updateVariant($itemVariant, $data);

            return response()->json([
                'message' => 'Variante mise à jour avec succès',
                'data' => new ItemVariantResource($variant),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Une erreur s'est produite lors de la mise à jour de la variante",
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Item $item, ItemVariant $itemVariant): JsonResponse
    {
        try {
            $this->service->deleteVariant($itemVariant, $item);

            return response()->json([
                'message' => 'Variante supprimée avec succès',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Une erreur s'est produite lors de la suppression de la variante",
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function toggleActive(Item $item, ItemVariant $itemVariant): JsonResponse
    {
        try {
            $variant = $this->service->toggleVariantActive($itemVariant);

            return response()->json([
                'message' => 'Statut de la variante mis à jour avec succès',
                'data' => new ItemVariantResource($variant),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Une erreur s'est produite lors de la mise à jour du statut de la variante",
                'error' => $e->getMessage(),
            ]);
        }
    }
}
