<?php

namespace App\Services\V1\Discount\Validators;

use App\Enum\V1\Discount\DiscountType;
use App\Exceptions\V1\Discount\DiscountTypeException;

class FirstOrderValidator implements DiscountTypeValidatorInterface
{

    public function supports(DiscountType $type): bool
    {
        return $type === DiscountType::FirstOrder;
    }

    /** @inheritDoc */
    public function validateValue(?int $value): void
    {
        if ($value < 0 || $value > 100) {
            throw DiscountTypeException::invalidPercentageValue($value);
        }
    }

    public function validateConfig(?array $config): void
    {

    }
}
