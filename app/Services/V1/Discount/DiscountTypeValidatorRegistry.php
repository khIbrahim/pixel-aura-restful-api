<?php

namespace App\Services\V1\Discount;

use App\Enum\V1\Discount\DiscountType;
use App\Exceptions\V1\Discount\DiscountTypeException;
use App\Services\V1\Discount\Validators\DiscountTypeValidatorInterface;

class DiscountTypeValidatorRegistry
{

    /** @var DiscountTypeValidatorInterface[] */
    private array $validators = [];

    public function __construct(iterable $validators){
        foreach ($validators as $validator){
            $this->registerValidator($validator);
        }
    }

    public function registerValidator(DiscountTypeValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }

    /**
     * @throws DiscountTypeException
     */
    public function getValidator(DiscountType $type): DiscountTypeValidatorInterface
    {
        foreach ($this->validators as $validator) {
            if ($validator->supports($type)) {
                return $validator;
            }
        }

        throw DiscountTypeException::unsupportedType($type->value);
    }

}
