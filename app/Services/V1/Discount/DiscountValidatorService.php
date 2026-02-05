<?php

namespace App\Services\V1\Discount;

use App\Contracts\V1\Discount\DiscountRepositoryInterface;
use App\DTO\V1\Discount\CreateDiscountDTO;
use App\Enum\V1\Discount\DiscountType;
use Carbon\Carbon;
use App\Exceptions\V1\Discount\{DiscountCreationException, DiscountTypeException};

readonly class DiscountValidatorService
{
    public function __construct(
        private DiscountRepositoryInterface   $repository,
        private DiscountTypeValidatorRegistry $typeValidatorRegistry,
    ) {}

    /**
     * Note: Les validations de format/structure sont déjà faites par CreateDiscountRequest
     *
     * @throws DiscountCreationException
     * @throws DiscountTypeException
     */
    public function validateCreation(CreateDiscountDTO $data): void
    {
        if ($data->code !== null) {
            $this->validateCodeUniqueness($data->code, $data->store_id);
        }

        $this->validateDiscountType($data->type, $data->value, $data->config);

        $this->validateDateConsistency($data->valid_from, $data->valid_until);
    }

    /**
     * @throws DiscountCreationException
     */
    private function validateCodeUniqueness(string $code, int $storeId): void
    {
        if ($this->repository->existsWhere([
            'code'     => $code,
            'store_id' => $storeId,
        ])) {
            throw DiscountCreationException::codeNotUnique($code);
        }
    }

    /**
     * @throws DiscountTypeException
     */
    private function validateDiscountType(DiscountType $type, ?float $value, ?array $config): void {
        $validator = $this->typeValidatorRegistry->getValidator($type);
        $validator->validateValue($value);
        $validator->validateConfig($config);
    }

    /**
     * @throws DiscountCreationException
     */
    private function validateDateConsistency(?Carbon $validFrom, ?Carbon $validUntil): void
    {
        if ($validFrom !== null && $validUntil !== null) {
            if ($validFrom->greaterThan($validUntil)) {
                throw DiscountCreationException::invalidDateRange($validFrom, $validUntil);
            }
        }
    }
}
