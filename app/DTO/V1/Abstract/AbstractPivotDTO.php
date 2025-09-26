<?php

namespace App\DTO\V1\Abstract;

readonly abstract class AbstractPivotDTO extends BaseDTO
{
    abstract public function getPivotKey(): int|string;
    abstract public function getPivotData(): array;

    public function toArray(): array
    {
        return $this->getPivotData();
    }
}
