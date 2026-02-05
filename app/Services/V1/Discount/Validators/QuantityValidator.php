<?php

namespace App\Services\V1\Discount\Validators;

use App\Enum\V1\Discount\DiscountType;
use App\Exceptions\V1\Discount\DiscountTypeException;
use App\ValueObjects\V1\DiscountConfig\DiscountQuantityConfig;
use Throwable;

class QuantityValidator implements DiscountTypeValidatorInterface
{

    public function supports(DiscountType $type): bool
    {
        return $type === DiscountType::Quantity;
    }

    public function validateValue(?int $value): void
    {

    }

    public function validateConfig(?array $config): void
    {
        if(empty($config)){
            throw DiscountTypeException::missingConfig("Quantity");
        }

        try {
            $quantityConfig = DiscountQuantityConfig::fromArray($config);
            if(! $quantityConfig->validate()){
                throw DiscountTypeException::invalidConfig("Quantity", $quantityConfig->getValidationErrors());
            }
        } catch(Throwable $e){
            throw DiscountTypeException::invalidConfig("Quantity", ['error' => $e->getMessage()]);
        }
    }
}
