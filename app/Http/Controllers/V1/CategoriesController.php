<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Category\CategoryServiceInterface;
use App\DTO\V1\Category\CreateCategoryDTO;
use App\Exceptions\V1\Category\CategoryCreationException;
use App\Exceptions\V1\Category\CategoryDeletionException;
use App\Exceptions\V1\Category\CategoryUpdateException;
use App\Exceptions\V1\Category\PositionDuplicateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Category\CreateCategoryRequest;
use App\Http\Requests\V1\Category\UpdateCategoryRequest;
use App\Http\Requests\V1\Category\ReorderCategoriesRequest;
use App\Http\Requests\V1\Category\ToggleCategoryActivationRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Hydrators\V1\Category\CategoryHydrator;
use App\Models\V1\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CategoriesController extends Controller
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService,
        private readonly CategoryHydrator         $hydrator
    ){}

    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->with('media')
            ->when($request->has('is_active'), function($query) use ($request){
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->when($request->filled('search'), function($query) use ($request){
                $query->where('name', 'ilike', '%', $request->get('search') . '%');
            })
            ->when($request->boolean('option_lists'), function($query) use ($request){
                $query->with('optionLists.options');
            })
            ->paginate($request->input('limit', 15));

        return CategoryResource::collection($categories)->response();
    }

    /**
     * POST /api/v1/categories
     */
    public function store(CreateCategoryRequest $request): JsonResponse
    {
        $dto = $this->hydrator->fromArray(array_merge($request->validated(), ['store_id' => $request->store()->id]), CreateCategoryDTO::class);

        try {
            $category = $this->categoryService->create($dto);

            return response()->json([
                'message' => 'La catégorie a bien été créée',
                'data'    => new CategoryResource($category),
            ], 201);

        } catch (CategoryCreationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('media');

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

        } catch (CategoryUpdateException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
    }

    public function destroy(Category $category): JsonResponse
    {
        try {
            $this->categoryService->delete($category);

            return response()->json([
                'message' => 'Catégorie supprimée'
            ]);
        } catch(CategoryDeletionException $e){
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
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
