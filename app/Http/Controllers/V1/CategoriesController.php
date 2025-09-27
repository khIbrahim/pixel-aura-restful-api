<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Category\CategoryServiceInterface;
use App\Exceptions\V1\Category\CategorySlugAlreadyExistsException;
use App\Exceptions\V1\Category\PositionDuplicateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Category\CreateCategoryRequest;
use App\Http\Requests\V1\Category\UpdateCategoryRequest;
use App\Http\Requests\V1\Category\ReorderCategoriesRequest;
use App\Http\Requests\V1\Category\ToggleCategoryActivationRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\V1\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CategoriesController extends Controller
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService,
    ){}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['is_active', 'search', 'parent_id', 'with']);
        $perPage = (int) $request->get('per_page', 25);
        $storeId = $request->user()->store_id;
        $categories = $this->categoryService->list($storeId, $filters, $perPage);

        return response()->json([
            'data' => CategoryResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
                'last_page'    => $categories->lastPage(),
            ]
        ]);
    }

    /**
     * POST /api/v1/categories
     */
    public function store(CreateCategoryRequest $request): JsonResponse
    {
        $dto = $request->toDTO(); //peut pas causer d'erreur en vrai

        try {
            $category = $this->categoryService->create($dto);

            return response()->json([
                'message' => 'La catégorie a bien été créée',
                'data'    => new CategoryResource($category),
            ], 201);

        } catch (CategorySlugAlreadyExistsException) {
            return response()->json([
                'message' => "Une catégorie avec ce slug " . str()->slug($dto->name) . " existe déjà"
            ], 409);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la catégorie',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => new CategoryResource($category)
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $dto = $request->toDTO();

        try {
            $updated = $this->categoryService->update($category, $dto);
            $updated->load(['parent','children']);

            return response()->json([
                'message' => 'La catégorie a bien été mise à jour',
                'data'    => new CategoryResource($updated),
            ]);

        } catch (CategorySlugAlreadyExistsException) {
            return response()->json([
                'message' => 'Slug déjà utilisé pour une autre catégorie'
            ], 409);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la catégorie',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return response()->json([
            'message' => 'Catégorie supprimée'
        ]);
    }

    public function reorder(ReorderCategoriesRequest $request): JsonResponse
    {
        $storeId = $request->user()->store_id;
        try {
            $this->categoryService->reorder($storeId, $request->idPositionMap());

            return response()->json([
                'message' => 'Réordonnancement appliqué'
            ]);
        } catch (PositionDuplicateException){
            return response()->json([
                'message' => 'Des positions dupliquées ont été détectées'
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors du réordonnancement',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function toggleActivation(ToggleCategoryActivationRequest $request, Category $category): JsonResponse
    {
        $updated = $this->categoryService->toggleActivation($category, $request->active($category));

        return response()->json([
            'message' => 'Statut mis à jour',
            'data'    => new CategoryResource($updated)
        ]);
    }

}
