<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreMember\StoreStoreRequest;
use App\Http\Resources\V1\StoreResource;
use App\Models\V1\Store;
use App\Services\V1\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Throwable;

class StoresController extends Controller
{

    public function __construct(
        private readonly StoreService $storeService
    ){}

    public function store(StoreStoreRequest $request): JsonResponse
    {
        try {
            $store = $this->storeService->createStore($request->validated());

            return response()->json([
                'message' => 'Store créé avec succès',
                'data' => new StoreResource($store),
            ], 201);

        } catch(ValidationException $e){
            return response()->json([
                'message' => 'Validation échouée',
                'errors'  => $e->errors()
            ]);
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
