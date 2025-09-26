<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Option\OptionServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Option\CreateOptionRequest;
use App\Http\Requests\V1\Option\UpdateOptionRequest;
use App\Http\Resources\V1\OptionResource;
use App\Hydrators\V1\Option\OptionHydrator;
use App\Models\V1\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OptionController extends Controller
{

    public function __construct(
        private readonly OptionServiceInterface $optionService,
        private readonly OptionHydrator         $optionHydrator
    ){}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['store_id', 'name', 'price_cents', 'price_cents_operator', 'is_active', 'search']);
        if(! isset($filters['store_id'])) {
            $filters['store_id'] = (int) $request->attributes->get('store')->id;
        }
        $perPage = (int) $request->get('per_page', 25);
        $options = $this->optionService->list($filters, $perPage);

        return response()->json([
            'data' => OptionResource::collection($options),
            'meta' => [
                'current_page' => $options->currentPage(),
                'per_page'     => $options->perPage(),
                'total'        => $options->total(),
                'last_page'    => $options->lastPage(),
            ]
        ]);
    }

    public function store(CreateOptionRequest $request): JsonResponse
    {
        try {
            $option = $this->optionService->create($this->optionHydrator->fromCreateRequest($request));

            return response()->json([
                'message' => "Option créé avec succès.",
                'data'    => new OptionResource($option)
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Une erreur est survenue lors de la création de l'option.",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(Option $option): JsonResponse
    {
        return response()->json([
            'data' => new OptionResource($option)
        ]);
    }

    public function update(UpdateOptionRequest $request, Option $option): JsonResponse
    {
        try {
            $option = $this->optionService->update($option, $this->optionHydrator->fromUpdateRequest($request));

            return response()->json([
                'message' => "Option mise à jour avec succès.",
                'data'    => new OptionResource($option)
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Une erreur est survenue lors de la mise à jour de l'option.",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Option $option): JsonResponse
    {
        try {
            $this->optionService->delete($option);

            return response()->json([
                'message' => "Option supprimée avec succès."
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Une erreur est survenue lors de la suppression de l'option.",
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
