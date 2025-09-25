<?php

namespace App\DTO\V1\Abstract;

use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractPivotDTO implements Arrayable
{
    abstract public function getPivotKey(): int|string;
    abstract public function getPivotData(): array;

    public function toArray(): array
    {
        return $this->getPivotData();
    }
}
