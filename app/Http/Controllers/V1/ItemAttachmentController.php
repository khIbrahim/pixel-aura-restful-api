<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Item\ItemAttachmentServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ItemAttachment\AttachIngredientsRequest;
use App\Http\Requests\V1\ItemAttachment\AttachOptionListsRequest;
use App\Http\Requests\V1\ItemAttachment\AttachOptionsRequest;
use App\Http\Resources\V1\IngredientResource;
use App\Http\Resources\V1\OptionListResource;
use App\Http\Resources\V1\OptionResource;
use App\Hydrators\V1\Ingredient\IngredientHydrator;
use App\Hydrators\V1\Option\OptionHydrator;
use App\Hydrators\V1\OptionList\OptionListHydrator;
use App\Models\V1\Ingredient;
use App\Models\V1\Item;
use App\Models\V1\Option;
use App\Models\V1\OptionList;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ItemAttachmentController extends Controller
{
    public function __construct(
        private readonly ItemAttachmentServiceInterface $itemAttachmentService,
        private readonly IngredientHydrator             $ingredientHydrator,
        private readonly OptionHydrator                 $optionHydrator,
        private readonly OptionListHydrator             $optionListHydrator
    ) {}

    // ==================== INGREDIENTS ====================

    /**
     * GET /api/v1/items/{item}/ingredients
     */
    public function indexIngredients(Item $item): JsonResponse
    {
        try {
            $ingredients = $item->load('ingredients')->ingredients;

            return response()->json([
                'data' => IngredientResource::collection($ingredients),
                'meta' => [
                    'total'   => $ingredients->count(),
                    'item_id' => $item->id,
                ]
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la récupération des ingrédients de l'item", [
                'item_id' => $item->id,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors de la récupération des ingrédients.",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/v1/items/{item}/ingredients
     */
    public function attachIngredients(AttachIngredientsRequest $request, Item $item): JsonResponse
    {
        try {
            $data = $this->ingredientHydrator->fromAttachRequest($request);
            $this->itemAttachmentService->attachIngredient($item, $data);

            Log::info("Ingrédients attachés à l'item", [
                'item_id' => $item->id,
                'ingredients_count' => count($data)
            ]);

            return response()->json([
                'message' => 'Ingrédients attachés avec succès',
                'data'    => IngredientResource::collection($item->load('ingredients')->ingredients)
            ], 201);

        } catch (Throwable $e) {
            Log::error("Erreur lors de l'attachement des ingrédients à l'item", [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Erreur lors de l'attachement des ingrédients",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/items/{item}/ingredients/{ingredient}
     */
    public function detachIngredient(Item $item, Ingredient $ingredient): JsonResponse
    {
        try {
            $this->itemAttachmentService->detachIngredient($item, $ingredient->id);

            Log::info("Ingrédient détaché de l'item", [
                'item_id'       => $item->id,
                'ingredient_id' => $ingredient->id
            ]);

            return response()->json([
                'message' => 'Ingrédient détaché avec succès'
            ]);

        } catch (Throwable $e) {
            Log::error("Erreur lors du détachement de l'ingrédient", [
                'item_id'       => $item->id,
                'ingredient_id' => $ingredient->id,
                'error'         => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors du détachement de l'ingrédient",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== OPTIONS ====================

    /**
     * GET /api/v1/items/{item}/options
     */
    public function indexOptions(Item $item): JsonResponse
    {
        try {
            $options = $item->load('options')->options;

            return response()->json([
                'data' => OptionResource::collection($options),
                'meta' => [
                    'total'   => $options->count(),
                    'item_id' => $item->id,
                ]
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la récupération des options de l'item", [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors de la récupération des options.",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/v1/items/{item}/options
     */
    public function attachOptions(AttachOptionsRequest $request, Item $item): JsonResponse
    {
        try {
            $data = $this->optionHydrator->fromAttachRequest($request);
            $this->itemAttachmentService->attachOption($item, $data);

            Log::info("Options attachées à l'item", [
                'item_id' => $item->id,
                'options_count' => count($data)
            ]);

            return response()->json([
                'message' => 'Options attachées avec succès',
                'data' => OptionResource::collection($item->load('options')->options)
            ], 201);

        } catch (Throwable $e) {
            Log::error("Erreur lors de l'attachement des options à l'item", [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors de l'attachement des options",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/items/{item}/options/{option}
     */
    public function detachOption(Item $item, Option $option): JsonResponse
    {
        try {
            $this->itemAttachmentService->detachOption($item, $option->id);

            Log::info("Option détachée de l'item", [
                'item_id' => $item->id,
                'option_id' => $option->id
            ]);

            return response()->json([
                'message' => 'Option détachée avec succès'
            ]);

        } catch (Throwable $e) {
            Log::error("Erreur lors du détachement de l'option", [
                'item_id' => $item->id,
                'option_id' => $option->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors du détachement de l'option",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== OPTION LISTS ====================

    /**
     * GET /api/v1/items/{item}/option-lists
     */
    public function indexOptionLists(Item $item): JsonResponse
    {
        try {
            $optionLists = $item->load('optionLists')->optionLists;

            return response()->json([
                'data' => OptionListResource::collection($optionLists),
                'meta' => [
                    'total' => $optionLists->count(),
                    'item_id' => $item->id,
                ]
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la récupération des listes d'options de l'item", [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors de la récupération des listes d'options.",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/v1/items/{item}/option-lists
     */
    public function attachOptionLists(AttachOptionListsRequest $request, Item $item): JsonResponse
    {
        try {
            $data = $this->optionListHydrator->fromAttachRequest($request);
            $this->itemAttachmentService->attachOptionList($item, $data);

            Log::info("Listes d'options attachées à l'item", [
                'item_id'            => $item->id,
                'option_lists_count' => count($data)
            ]);

            return response()->json([
                'message' => 'Listes d\'options attachées avec succès',
                'data'    => OptionListResource::collection($item->load('optionLists')->optionLists)
            ], 201);

        } catch (Throwable $e) {
            Log::error("Erreur lors de l'attachement des listes d'options à l'item", [
                'item_id' => $item->id,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors de l'attachement des listes d'options",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/items/{item}/option-lists/{optionList}
     */
    public function detachOptionList(Item $item, OptionList $optionList): JsonResponse
    {
        try {
            $this->itemAttachmentService->detachOptionList($item, $optionList->id);

            Log::info("Liste d'options détachée de l'item", [
                'item_id' => $item->id,
                'option_list_id' => $optionList->id
            ]);

            return response()->json([
                'message' => 'Liste d\'options détachée avec succès'
            ]);

        } catch (Throwable $e) {
            Log::error("Erreur lors du détachement de la liste d'options", [
                'item_id' => $item->id,
                'option_list_id' => $optionList->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors du détachement de la liste d'options",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
