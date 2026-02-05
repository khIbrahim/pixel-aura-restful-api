<?php

namespace App\Traits\V1\DiscountConfig;

trait DiscountConfigTrait
{

    private array $validationErrors = [];

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

}
