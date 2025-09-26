<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Item\ItemServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Item\UpdateItemRequest;
use App\Http\Requests\V1\StoreMember\StoreItemRequest;
use App\Http\Resources\V1\IngredientResource;
use App\Http\Resources\V1\ItemResource;
use App\Hydrators\V1\Item\ItemHydrator;
use App\Models\V1\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ItemsController extends Controller
{

    public function __construct(
        private readonly ItemServiceInterface $itemService,
    ){}

    /**
     * POST /api/v1/items
     */
    public function store(StoreItemRequest $request, ItemHydrator $hydrator): JsonResponse
    {
        $dto = $hydrator->fromRequest($request);

        try {
            $item = $this->itemService->create($dto);
            $item->load('category', 'tax', 'creator', 'variants', 'ingredients', 'options');

            return response()->json([
                'message' => "Item créé avec succès",
                'data'    => new ItemResource($item)
            ]);
        } catch (Throwable $e){
            return response()->json([
                'message' => "Erreur lors de la création de l'item",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/items/{item}
     */
    public function show(Item $item): JsonResponse
    {
        return response()->json([
            'data' => new ItemResource($item->load('category', 'tax', 'creator', 'variants', 'ingredients', 'options', 'media'))
        ]);
    }

    /**
     * GET /api/v1/items/{item}/ingredients
     */
    public function listIngredients(Item $item): JsonResponse
    {
        return response()->json([
            'data' => IngredientResource::collection($item->load('ingredients')->ingredients)
        ]);
    }

    /**
     * GET /api/v1/items/{item}/options
     */
    public function listOptions(Item $item): JsonResponse
    {
        return response()->json([
            'data' => IngredientResource::collection($item->load('options')->options)
        ]);
    }

    /**
     * GET /api/v1/items/{item}/variants
     */
    public function listVariants(Item $item): JsonResponse
    {
        return response()->json([
            'data' => IngredientResource::collection($item->load('variants')->variants)
        ]);
    }

    /**
     * PUT /api/v1/items/{item}
     */
    public function update(UpdateItemRequest $request, Item $item)
    {

    }

    /**
     * DELETE /api/v1/items/{item}
     */
    public function destroy(Item $item)
    {

    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['is_active', 'search', 'with', 'category_id']);
        $perPage = (int) $request->get('per_page', 25);
        $storeId = $request->user()->store_id;
        $categories = $this->itemService->list($storeId, $filters, $perPage);

        return response()->json([
            'data' => ItemResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
                'last_page'    => $categories->lastPage(),
            ]
        ]);
    }


}
