<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\OptionList\OptionListServiceInterface;
use App\Exceptions\V1\OptionList\OptionListCreationException;
use App\Exceptions\V1\OptionList\OptionListDeletionException;
use App\Exceptions\V1\OptionList\OptionListUpdateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OptionList\CreateOptionListRequest;
use App\Http\Requests\V1\OptionList\UpdateOptionListRequest;
use App\Http\Resources\V1\OptionListResource;
use App\Hydrators\V1\OptionList\OptionListHydrator;
use App\Models\V1\OptionList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            $optionList->load(['options', 'items']);

            return response()->json([
                'message' => "La liste d'options a été créée avec succès.",
                'data'    => new OptionListResource($optionList),
            ], 201);
        } catch (OptionListCreationException $e){
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
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

            return response()->json([
                'message' => "La liste d'options a été mise à jour avec succès.",
                'data'    => new OptionListResource($updatedOptionList),
            ]);
        } catch (OptionListUpdateException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
    }

    public function destroy(OptionList $optionList): JsonResponse
    {
        try {
            $this->optionListService->delete($optionList);

            return response()->json([
                'message' => "La liste d'options a été supprimée avec succès.",
            ]);
        } catch (OptionListDeletionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
    }

}
