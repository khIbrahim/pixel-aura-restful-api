<?php

namespace App\Services\V1\Discount\Validators;

use App\Enum\V1\Discount\DiscountType;
use App\Exceptions\V1\Discount\DiscountTypeException;

class ReduceDeliveryValidator implements DiscountTypeValidatorInterface
{

    public function supports(DiscountType $type): bool
    {
        return $type === DiscountType::ReduceDelivery;
    }

    /** @throws DiscountTypeException */
    public function validateValue(?int $value): void
    {
        if($value < 1 || $value > 100){
            throw DiscountTypeException::invalidPercentageValue($value);
        }
    }

    public function validateConfig(?array $config): void
    {

    }
}
