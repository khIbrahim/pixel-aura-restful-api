<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Discount\DiscountServiceInterface;
use App\Exceptions\V1\Discount\DiscountCreationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Discount\CreateDiscountRequest;
use App\Http\Resources\V1\DiscountResource;
use App\Hydrators\V1\Discount\DiscountHydrator;
use Illuminate\Http\JsonResponse;

class DiscountsController extends Controller
{

    public function __construct(
        private readonly DiscountServiceInterface $discountService,
        private readonly DiscountHydrator $hydrator,
    ){}

    public function store(CreateDiscountRequest $request): JsonResponse
    {
        try {
            $discount = $this->discountService->create($this->hydrator->fromCreateRequest($request));

            return response()->json([
                'message' => "Le discount a bien été créé.",
                'data'    => new DiscountResource($discount),
            ]);
        } catch(DiscountCreationException $e){
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
    }

}
