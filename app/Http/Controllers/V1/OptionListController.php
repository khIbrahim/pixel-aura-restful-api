<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\OptionList\OptionListServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OptionList\StoreOptionListRequest;
use App\Http\Requests\V1\OptionList\UpdateOptionListRequest;
use App\Http\Resources\V1\OptionListResource;
use App\Hydrators\V1\OptionList\OptionListHydrator;
use App\Hydrators\V1\OptionList\UpdateOptionListHydrator;
use App\Models\V1\OptionList;
use App\Models\V1\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class OptionListController extends Controller
{
    public function __construct(
        private readonly OptionListServiceInterface $optionListService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['is_active', 'search', 'with', 'min_selections', 'max_selections']);
        $perPage = (int) $request->get('per_page', 25);
        $storeId = $request->user()->store_id;

        $optionLists = $this->optionListService->list($storeId, $filters, $perPage);

        return response()->json([
            'data' => OptionListResource::collection($optionLists),
            'meta' => [
                'current_page' => $optionLists->currentPage(),
                'per_page'     => $optionLists->perPage(),
                'total'        => $optionLists->total(),
                'last_page'    => $optionLists->lastPage(),
            ]
        ]);
    }

    public function store(StoreOptionListRequest $request, OptionListHydrator $hydrator): JsonResponse
    {
        $data = $hydrator->fromRequest($request);

        try {
            $optionList = $this->optionListService->create($data);

            Log::info("Liste d'options créée", [
                'option_list_id' => $optionList->id,
                'store_id' => $optionList->store_id,
                'name' => $optionList->name
            ]);

            return response()->json([
                'message' => "La liste d'options a été créée avec succès.",
                'data'    => new OptionListResource($optionList),
            ], 201);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la création de la liste d'options", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Une erreur s'est produite lors de la création de la liste d'options.",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(OptionList $optionList): JsonResponse
    {
        return response()->json([
            'data' => new OptionListResource($optionList->load('options', 'activeOptions')),
        ]);
    }

    public function update(UpdateOptionListRequest $request, OptionList $optionList, UpdateOptionListHydrator $hydrator): JsonResponse
    {
        $data = $hydrator->fromRequest($request);

        try {
            $updatedOptionList = $this->optionListService->update($optionList, $data);

            Log::info("Liste d'options mise à jour", [
                'option_list_id' => $updatedOptionList->id,
                'store_id' => $updatedOptionList->store_id
            ]);

            return response()->json([
                'message' => "La liste d'options a été mise à jour avec succès.",
                'data'    => new OptionListResource($updatedOptionList),
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la mise à jour de la liste d'options", [
                'option_list_id' => $optionList->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Une erreur s'est produite lors de la mise à jour de la liste d'options.",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(OptionList $optionList): JsonResponse
    {
        try {
            $result = $this->optionListService->delete($optionList);

            Log::info("Liste d'options supprimée", [
                'option_list_id' => $optionList->id,
                'store_id' => $optionList->store_id
            ]);

            return response()->json([
                'message' => "La liste d'options a été supprimée avec succès.",
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la suppression de la liste d'options", [
                'option_list_id' => $optionList->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Une erreur s'est produite lors de la suppression de la liste d'options.",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $term    = $request->get('q', '');
        $filters = $request->only(['is_active', 'min_selections', 'max_selections']);
        $storeId = $request->user()->store_id;

        if (empty($term)) {
            return response()->json([
                'message' => 'Le terme de recherche est requis.',
            ], 400);
        }

        try {
            $optionLists = $this->optionListService->search($storeId, $term, $filters);

            return response()->json([
                'data' => OptionListResource::collection($optionLists),
                'meta' => [
                    'total' => $optionLists->count(),
                    'term'  => $term,
                ]
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la recherche de listes d'options", [
                'term' => $term,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Une erreur s'est produite lors de la recherche.",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'option_list_ids' => ['required', 'array'],
            'option_list_ids.*' => ['required', 'integer', 'exists:option_lists,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        try {
            $updatedCount = $this->optionListService->bulkUpdateStatus(
                $request->input('option_list_ids'),
                $request->input('is_active')
            );

            Log::info("Mise à jour en lot du statut des listes d'options", [
                'updated_count' => $updatedCount,
                'is_active' => $request->input('is_active')
            ]);

            return response()->json([
                'message' => "{$updatedCount} liste(s) d'options mise(s) à jour avec succès.",
                'updated_count' => $updatedCount,
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la mise à jour en lot", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Une erreur s'est produite lors de la mise à jour en lot.",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Attach option lists to an item
     */
    public function attachToItem(Request $request, Item $item): JsonResponse
    {
        $request->validate([
            'option_lists' => ['required', 'array'],
            'option_lists.*.option_list_id' => ['required', 'integer', 'exists:option_lists,id'],
            'option_lists.*.is_required' => ['boolean'],
            'option_lists.*.min_selections' => ['integer', 'nullable'],
            'option_lists.*.max_selections' => ['integer', 'nullable'],
            'option_lists.*.display_order' => ['integer', 'nullable'],
            'option_lists.*.is_active' => ['boolean'],
        ]);

        try {
            $optionLists = $request->input('option_lists');

            foreach ($optionLists as $optionListData) {
                $optionList = OptionList::findOrFail($optionListData['option_list_id']);
                $pivotData = array_filter([
                    'store_id' => $request->user()->store_id,
                    'is_required' => $optionListData['is_required'] ?? false,
                    'min_selections' => $optionListData['min_selections'] ?? null,
                    'max_selections' => $optionListData['max_selections'] ?? null,
                    'display_order' => $optionListData['display_order'] ?? 0,
                    'is_active' => $optionListData['is_active'] ?? true,
                ]);

                $this->optionListService->attachToItem($optionList, $item->id, $pivotData);
            }

            Log::info("Option lists attachées à l'item", [
                'item_id' => $item->id,
                'option_lists_count' => count($optionLists)
            ]);

            return response()->json([
                'message' => 'Listes d\'options attachées avec succès à l\'item.',
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de l'attachement des option lists à l'item", [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Erreur lors de l'attachement des listes d'options.",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detach option list from an item
     */
    public function detachFromItem(OptionList $optionList, Item $item): JsonResponse
    {
        try {
            $this->optionListService->detachFromItem($optionList, $item->id);

            Log::info("Option list détachée de l'item", [
                'option_list_id' => $optionList->id,
                'item_id' => $item->id
            ]);

            return response()->json([
                'message' => 'Liste d\'options détachée avec succès de l\'item.',
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors du détachement de l'option list de l'item", [
                'option_list_id' => $optionList->id,
                'item_id' => $item->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Erreur lors du détachement de la liste d'options.",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get option lists for a specific item
     */
    public function getItemOptionLists(Item $item): JsonResponse
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
            Log::error("Erreur lors de la récupération des option lists de l'item", [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => "Erreur lors de la récupération des listes d'options.",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
