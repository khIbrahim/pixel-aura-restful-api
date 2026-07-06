<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Category\CategoryOptionListsServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ItemAttachment\AttachOptionListsRequest;
use App\Http\Resources\V1\OptionListResource;
use App\Hydrators\V1\OptionList\OptionListHydrator;
use App\Models\V1\Category;
use App\Models\V1\OptionList;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class CategoryOptionListsController extends Controller
{

    public function __construct(
        private readonly OptionListHydrator                  $optionListHydrator,
        private readonly CategoryOptionListsServiceInterface $service
    ){}

    /**
     * GET /api/v1/categories/{category}/option-lists
     */
    public function index(Category $category): JsonResponse
    {
        try {
            $optionLists = $category->optionLists()
                ->with('options')
                ->get();

            return response()->json([
                'data' => OptionListResource::collection($optionLists),
                'meta' => [
                    'category_id' => $category->id,
                    'total'       => count($optionLists)
                ]
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la récupération des listes d'options de la catégorie", [
                'category_id' => $category->id,
                'error'       => $e->getMessage()
            ]);

            return response()->json([
                'message'     => "Erreur lors de la récupération des listes d'options de la catégorie",
                'error'       => $e->getMessage(),
                'category_id' => $category->id,
            ], 500);
        }
    }

    /**
     * POST /api/v1/categories/{category}/option-lists
     */
    public function attach(AttachOptionListsRequest $request, Category $category): JsonResponse
    {
        try {
            $data = $this->optionListHydrator->fromAttachRequest($request);
            $this->service->attach($category, $data);

            Log::info("Liste d'options attachée à la catégorie", [
                'category_id'        => $category->id,
                'option_lists_count' => count($data)
            ]);

            return response()->json([
                'message' => 'Listes d\'options attachées avec succès',
                'data'    => OptionListResource::collection($category->load('optionLists')->optionLists)
            ], 201);
        } catch (Throwable $e) {
            Log::error("Erreur lors de l'attachement des listes d'options à la catégorie", [
                'category_id' => $category->id,
                'error'       => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors de l'attachement des listes d'options",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/categories/{category}/option-lists
     */
    public function detach(Category $category, OptionList $optionList): JsonResponse
    {
        try {
            $this->service->detach($category, $optionList->id);

            Log::info("Liste d'options détachée de la catégorie", [
                'category_id'    => $category->id,
                'option_list_id' => $optionList->id
            ]);

            return response()->json([
                'message' => 'Liste d\'options détachée avec succès'
            ]);

        } catch (Throwable $e) {
            Log::error("Erreur lors du détachement de la liste d'options de la catégorie", [
                'category_id'    => $category->id,
                'option_list_id' => $optionList->id,
                'error'          => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors du détachement de la liste d'options",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
