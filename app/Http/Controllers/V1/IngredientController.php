<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Ingredient\IngredientServiceInterface;
use App\DTO\V1\Ingredient\CreateIngredientDTO;
use App\DTO\V1\Ingredient\UpdateIngredientDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Ingredient\StoreIngredientRequest;
use App\Http\Requests\V1\Ingredient\UpdateIngredientRequest;
use App\Http\Resources\V1\IngredientResource;
use App\Models\V1\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class IngredientController extends Controller
{

    public function __construct(
        private readonly IngredientServiceInterface $ingredientService,
    ){}

    public function index(Request $request): JsonResponse
    {
        $filters     = $request->only(['store_id', 'is_allergen', 'is_mandatory', 'is_active', 'search']);
        if(! isset($filters['store_id'])) {
            $filters['store_id'] = (int) $request->attributes->get('store')->id;
        }
        $perPage     = (int) $request->get('per_page', 25);
        $ingredients = $this->ingredientService->list($filters, $perPage);

        return response()->json([
            'data' => IngredientResource::collection($ingredients),
            'meta' => [
                'current_page' => $ingredients->currentPage(),
                'per_page'     => $ingredients->perPage(),
                'total'        => $ingredients->total(),
                'last_page'    => $ingredients->lastPage(),
            ]
        ]);
    }

    public function show(Ingredient $ingredient): JsonResponse
    {
        return response()->json(new IngredientResource($ingredient));
    }

    public function update(UpdateIngredientRequest $request, Ingredient $ingredient): JsonResponse
    {
        try {
            $ingredient = $this->ingredientService->update($ingredient, UpdateIngredientDTO::fromArray($request->validated()));

            return response()->json([
                'message' => "Ingrédient mis à jour avec succès.",
                'data'    => new IngredientResource($ingredient)
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Une erreur est survenue lors de la mise à jour de l'ingrédient.",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreIngredientRequest $request): JsonResponse
    {
        try {
            $ingredient = $this->ingredientService->create(CreateIngredientDTO::fromArray(array_merge(
                ['store_id' => (int) $request->attributes->get('store')->id],
                $request->validated()
            )));

            return response()->json([
                'message' => "Ingrédient créé avec succès.",
                'data'    => new IngredientResource($ingredient)
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Une erreur est survenue lors de la création de l'ingrédient.",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        $deleted = $this->ingredientService->destroy($ingredient);
        if ($deleted) {
            return response()->json([
                'message' => "Ingrédient supprimé avec succès."
            ]);
        } else {
            return response()->json([
                'message' => "Une erreur est survenue lors de la suppression de l'ingrédient."
            ], 500);
        }
    }

}
