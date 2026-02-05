<?php

namespace App\Contracts\V1\Discount;

use App\DTO\V1\Discount\CreateDiscountDTO;
use App\Exceptions\V1\Discount\DiscountCreationException;
use App\Models\V1\Discount;

interface DiscountServiceInterface
{

    /** @throws DiscountCreationException */
    public function create(CreateDiscountDTO $data): Discount;

}
