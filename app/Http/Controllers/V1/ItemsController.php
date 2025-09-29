<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Item\ItemServiceInterface;
use App\Exceptions\V1\Item\ItemCreationException;
use App\Exceptions\V1\Item\ItemDeletionException;
use App\Exceptions\V1\Item\ItemUpdateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Item\UpdateItemRequest;
use App\Http\Requests\V1\StoreMember\CreateItemRequest;
use App\Http\Resources\V1\ItemResource;
use App\Hydrators\V1\Item\ItemHydrator;
use App\Models\V1\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function __construct(
        private readonly ItemServiceInterface $itemService,
        private readonly ItemHydrator $itemHydrator,
    ) {}

    /**
     * POST /api/v1/items
     */
    public function store(CreateItemRequest $request): JsonResponse
    {
        $dto = $this->itemHydrator->fromCreateRequest($request);

        try {
            $item = $this->itemService->create($dto);
            $item->load('category', 'tax', 'creator', 'variants', 'ingredients', 'options');

            return response()->json([
                'message' => 'Item créé avec succès',
                'data' => new ItemResource($item),
            ]);
        } catch (ItemCreationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
    }

    /**
     * GET /api/v1/items/{item}
     */
    public function show(Item $item): JsonResponse
    {
        return response()->json([
            'data' => new ItemResource($item->load('category', 'tax', 'creator', 'variants', 'ingredients', 'options', 'media')),
        ]);
    }

    /**
     * PUT/PATCH /api/v1/items/{item}
     */
    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        $dto = $this->itemHydrator->fromUpdateRequest($request, $item);

        try {
            $updatedItem = $this->itemService->update($dto, $item);

            return response()->json([
                'message' => 'Item mis à jour avec succès',
                'data' => new ItemResource($updatedItem),
            ]);
        } catch (ItemUpdateException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
    }

    /**
     * DELETE /api/v1/items/{item}
     */
    public function destroy(Item $item): JsonResponse
    {
        try {
            $this->itemService->delete($item);

            return response()->json([
                'message' => 'Item supprimé avec succès',
            ]);
        } catch (ItemDeletionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
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
            ],
        ]);
    }
}
