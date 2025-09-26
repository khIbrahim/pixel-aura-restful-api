<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreMember\CreateStoreRequest;
use App\Http\Resources\V1\StoreResource;
use App\Hydrators\V1\Store\StoreHydrator;
use App\Models\V1\Store;
use App\Services\V1\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class StoresController extends Controller
{

    public function __construct(
        private readonly StoreService  $storeService,
        private readonly StoreHydrator $storeHydrator
    ){}

    public function store(CreateStoreRequest $request): JsonResponse
    {
        $dto = $this->storeHydrator->fromCreateRequest($request);

        try {
            $store = $this->storeService->create($dto);

            return response()->json([
                'message' => 'Store créé avec succès',
                'data' => new StoreResource($store),
            ], 201);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du store',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(): AnonymousResourceCollection
    {
        $stores = Store::query()
            ->with('owner')
            ->withCount('members')
            ->paginate(20);

        return StoreResource::collection($stores);
    }

    public function show(Store $store): StoreResource
    {
        return new StoreResource($store);
    }

}
