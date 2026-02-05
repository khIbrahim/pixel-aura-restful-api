<?php

namespace App\Services\V1\Discount\Validators;

use App\Enum\V1\Discount\DiscountType;
use App\Exceptions\V1\Discount\DiscountTypeException;

class FixedAmountValidator implements DiscountTypeValidatorInterface
{

    public function supports(DiscountType $type): bool
    {
        return $type === DiscountType::FixedAmount;
    }

    /** @inheritDoc */
    public function validateValue(?int $value): void
    {
        if($value === null || $value < 1) {
            throw DiscountTypeException::invalidFixedAmountValue($value);
        }
    }

    public function validateConfig(?array $config): void
    {

    }
}
