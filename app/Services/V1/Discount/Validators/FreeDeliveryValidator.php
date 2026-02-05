<?php

namespace App\Services\V1\Discount\Validators;

use App\Enum\V1\Discount\DiscountType;

class FreeDeliveryValidator implements DiscountTypeValidatorInterface
{

    public function supports(DiscountType $type): bool
    {
        return $type === DiscountType::FreeDelivery;
    }

    public function validateValue(?int $value): void
    {
    }

    public function validateConfig(?array $config): void
    {

    }
}
