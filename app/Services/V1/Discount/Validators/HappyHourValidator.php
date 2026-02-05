<?php

namespace App\Services\V1\Discount\Validators;

use App\Enum\V1\Discount\DiscountType;
use App\Exceptions\V1\Discount\DiscountTypeException;
use App\ValueObjects\V1\DiscountConfig\HappyHourConfig;
use Throwable;

class HappyHourValidator implements DiscountTypeValidatorInterface
{

    public function supports(DiscountType $type): bool
    {
        return $type === DiscountType::HappyHour;
    }

    public function validateValue(?int $value): void
    {
        if ($value < 0 || $value > 100) {
            throw DiscountTypeException::invalidPercentageValue($value);
        }
    }

    public function validateConfig(?array $config): void
    {
        if (empty($config)) {
            throw DiscountTypeException::configRequired('HappyHour');
        }

        try {
            $configObject = HappyHourConfig::fromArray($config);

            if (! $configObject->validate()) {
                $errors = $configObject->getValidationErrors();
                throw DiscountTypeException::invalidConfig('HappyHour', $errors);
            }
        } catch (Throwable $e) {
            throw DiscountTypeException::invalidConfig('HappyHour', ['error' => $e->getMessage()]);
        }
    }
}
