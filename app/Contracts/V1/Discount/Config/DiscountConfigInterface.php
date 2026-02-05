<?php

namespace App\Contracts\V1\Discount\Config;

use App\Enum\V1\Discount\DiscountType;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

interface DiscountConfigInterface extends Arrayable, JsonSerializable
{

    public function validate(): bool;

    public function getValidationErrors(): array;

    public function toArray(): array;

    public function jsonSerialize(): array;

    public static function fromArray(array $data): static;

    public function getType(): DiscountType;

}
