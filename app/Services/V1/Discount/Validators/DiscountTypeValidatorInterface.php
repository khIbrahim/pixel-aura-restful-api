<?php

namespace App\Services\V1\Discount\Validators;

use App\Enum\V1\Discount\DiscountType;
use App\Exceptions\V1\Discount\DiscountTypeException;

interface DiscountTypeValidatorInterface
{

    public function supports(DiscountType $type): bool;

    /**
     * @param int|null $value
     * @return void
     * @throws DiscountTypeException
     */
    public function validateValue(?int $value): void;

    /**
     * @param array|null $config
     * @return void
     * @throws DiscountTypeException
     */
    public function validateConfig(?array $config): void;

}
