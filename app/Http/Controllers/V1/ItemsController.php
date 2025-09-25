<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Item\ItemAttachmentServiceInterface;
use App\Contracts\V1\Item\ItemServiceInterface;
use App\DTO\V1\Option\OptionPivotDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Ingredient\AttachIngredientsRequest;
use App\Http\Requests\V1\Ingredient\DetachIngredientsRequest;
use App\Http\Requests\V1\Item\UpdateItemRequest;
use App\Http\Requests\V1\Items\AttachOptionsRequest;
use App\Http\Requests\V1\StoreMember\StoreItemRequest;
use App\Http\Resources\V1\IngredientResource;
use App\Http\Resources\V1\ItemResource;
use App\Http\Resources\V1\OptionResource;
use App\Hydrators\V1\Ingredient\IngredientsAttachHydrator;
use App\Hydrators\V1\Item\ItemHydrator;
use App\Hydrators\V1\Option\OptionsAttachHydrator;
use App\Models\V1\Item;
use App\Models\V1\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ItemsController extends Controller
{

    public function __construct(
        private readonly ItemServiceInterface $itemService,
        private readonly ItemAttachmentServiceInterface $itemAttachmentService
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

    public function attachOptions(AttachOptionsRequest $request, Item $item, OptionsAttachHydrator $hydrator): JsonResponse
    {
        try {
            $data = $hydrator->fromRequest($request);
            $options = $this->itemAttachmentService->attachOptions($item, $data);

            Log::info("Options attachées à l'item", [
                'item_id' => $item->id,
                'options' => array_map(fn(OptionPivotDTO $dto) => $dto->option_id, $data)
            ]);

            return response()->json([
                'message' => 'Options attachées avec succès',
                'data' => OptionResource::collection($options, true)
            ]);

        } catch (Throwable $e) {
            Log::error("Erreur lors de l'attachement des options à l'item", [
                'item_id' => $item->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Erreur lors de l'attachement des options",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function detachOption(Item $item, Option $option): JsonResponse
    {
        try {
            $this->itemAttachmentService->detachOption($item, $option);

            Log::info("Option détachée de l'item", [
                'item_id' => $item->id,
                'option'  => $option->id
            ]);

            return response()->json([
                'message' => 'Option détachée avec succès',
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors du détachement de l'option option de l'item", [
                'item_id' => $item->id,
                'option'  => $option->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Erreur lors du détachement de l'option",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function attachIngredients(AttachIngredientsRequest $request, Item $item, IngredientsAttachHydrator $hydrator): JsonResponse
    {
        try {
            $data        = $hydrator->fromRequest($request);
            $ingredients = $this->itemAttachmentService->attachIngredients($item, $data);

            Log::info("Ingrédients attachés à l'item", [
                'item_id' => $item->id,
                'ingredients' => array_map(fn($dto) => $dto->ingredientId, $data)
            ]);

            return response()->json([
                'message' => 'Ingrédients attachés avec succès',
                'data' => IngredientResource::collection($ingredients)
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de l'attachement des ingrédients à l'item", [
                'item_id' => $item->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Erreur lors de l'attachement des ingrédients",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function detachIngredient(Item $item, \App\Models\V1\Ingredient $ingredient): JsonResponse
    {
        try {
            $this->itemAttachmentService->detachIngredient($item, $ingredient);

            Log::info("Ingrédient détaché de l'item", [
                'item_id' => $item->id,
                'ingredient_id' => $ingredient->id
            ]);

            return response()->json([
                'message' => 'Ingrédient détaché avec succès',
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors du détachement de l'ingrédient de l'item", [
                'item_id' => $item->id,
                'ingredient_id' => $ingredient->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Erreur lors du détachement de l'ingrédient",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function detachIngredients(DetachIngredientsRequest $request, Item $item): JsonResponse
    {
        try {
            $ingredientIds = $request->input('ingredient_ids');
            $this->itemAttachmentService->detachIngredients($item, $ingredientIds);

            Log::info("Ingrédients détachés de l'item", [
                'item_id' => $item->id,
                'ingredient_ids' => $ingredientIds
            ]);

            return response()->json([
                'message' => 'Ingrédients détachés avec succès',
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors du détachement des ingrédients de l'item", [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Erreur lors du détachement des ingrédients",
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
