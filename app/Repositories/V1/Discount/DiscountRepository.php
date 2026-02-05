<?php

namespace App\Repositories\V1\Discount;

use App\Contracts\V1\Discount\DiscountRepositoryInterface;
use App\Models\V1\Discount;
use App\Repositories\V1\BaseRepository;

class DiscountRepository extends BaseRepository implements DiscountRepositoryInterface
{

    public function model(): string
    {
        return Discount::class;
    }
}
