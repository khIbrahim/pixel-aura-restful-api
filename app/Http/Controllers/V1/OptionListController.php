<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\OptionList\OptionListServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OptionList\CreateOptionListRequest;
use App\Http\Requests\V1\OptionList\UpdateOptionListRequest;
use App\Http\Resources\V1\OptionListResource;
use App\Hydrators\V1\OptionList\OptionListHydrator;
use App\Models\V1\OptionList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class OptionListController extends Controller
{
    public function __construct(
        private readonly OptionListServiceInterface $optionListService,
        private readonly OptionListHydrator         $hydrator
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

    public function store(CreateOptionListRequest $request): JsonResponse
    {
        $data = $this->hydrator->fromCreateRequest($request);

        try {
            $optionList = $this->optionListService->create($data);

            Log::info("Liste d'options créée", [
                'option_list_id' => $optionList->id,
                'store_id'       => $optionList->store_id,
                'name'           => $optionList->name
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
            'data' => new OptionListResource($optionList->load('options')),
        ]);
    }

    public function update(UpdateOptionListRequest $request, OptionList $optionList): JsonResponse
    {
        try {
            $data              = $this->hydrator->fromUpdateRequest($request);
            $updatedOptionList = $this->optionListService->update($optionList, $data);

            Log::info("Liste d'options mise à jour", [
                'option_list_id' => $updatedOptionList->id,
                'store_id'       => $updatedOptionList->store_id
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
                'store_id'       => $optionList->store_id,
                'result'         => $result
            ]);

            return response()->json([
                'message' => "La liste d'options a été supprimée avec succès.",
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la suppression de la liste d'options", [
                'option_list_id' => $optionList->id,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => "Une erreur s'est produite lors de la suppression de la liste d'options.",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
