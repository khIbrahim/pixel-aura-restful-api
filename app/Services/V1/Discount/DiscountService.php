<?php

namespace App\Services\V1\Discount;

use App\Contracts\V1\Discount\DiscountRepositoryInterface;
use App\Contracts\V1\Discount\DiscountServiceInterface;
use App\DTO\V1\Discount\CreateDiscountDTO;
use App\Events\V1\Discount\DiscountCreated;
use App\Exceptions\V1\Discount\DiscountCreationException;
use App\Models\V1\Discount;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class DiscountService implements DiscountServiceInterface
{

    public function __construct(
        private DiscountRepositoryInterface $repository,
        private DiscountValidatorService    $validator,
    ){}

    /** @inheritDoc */
    public function create(CreateDiscountDTO $data): Discount
    {
        try {
            $this->validator->validateCreation($data);

            Log::info("Création du discount :", [
                'data' => $data->toArray()
            ]);

            /** @var Discount $discount */
            $discount = $this->repository->create($data->toArray());

            Log::info("Discount créé avec succès :", [
                'id'   => $discount->id,
                'code' => $discount->code,
            ]);

            broadcast(new DiscountCreated($discount))->toOthers();

            return $discount;
        } catch(Throwable $e){
            Log::error("Erreur lors de la création du discount :", [
                'message' => $e->getMessage(),
                'data'    => $data->toArray(),
                'trace'   => $e->getTraceAsString()
            ]);

            if($e instanceof DiscountCreationException){
                throw $e;
            }

            throw DiscountCreationException::default($e);
        }
    }
}
