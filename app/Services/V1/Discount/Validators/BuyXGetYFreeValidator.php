<?php

namespace App\Services\V1\Discount\Validators;

use App\Enum\V1\Discount\DiscountType;
use App\Exceptions\V1\Discount\DiscountTypeException;
use App\ValueObjects\V1\DiscountConfig\BuyXGetYConfig;
use Throwable;

class BuyXGetYFreeValidator implements DiscountTypeValidatorInterface
{

    public function supports(DiscountType $type): bool
    {
        return $type === DiscountType::BuyXGetYFree;
    }

    public function validateValue(?int $value): void
    {
    }

    /** @inheritDoc */
    public function validateConfig(?array $config): void
    {
        if (empty($config)) {
            throw DiscountTypeException::missingConfig('BuyXGetYFree');
        }

        try {
            $configObject = BuyXGetYConfig::fromArray($config);

            if (! $configObject->validate()) {
                throw DiscountTypeException::invalidConfig('BuyXGetYFree', $configObject->getValidationErrors());
            }
        } catch (Throwable $e) {
            throw DiscountTypeException::invalidConfig('BuyXGetYFree', ['error' => $e->getMessage()]);
        }
    }
}
