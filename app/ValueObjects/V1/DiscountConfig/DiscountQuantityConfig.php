<?php

namespace App\ValueObjects\V1\DiscountConfig;

use App\Contracts\V1\Discount\Config\DiscountConfigInterface;
use App\Enum\V1\Discount\DiscountType;
use App\Traits\V1\DiscountConfig\DiscountConfigTrait;

class DiscountQuantityConfig implements DiscountConfigInterface
{
    use DiscountConfigTrait;

    public function __construct(
        public ?int $min_quantity       = null,
        public ?int $max_quantity       = null,
        public ?float $discount_percent = null,
    ){}

    public function validate(): bool
    {
        if($this->min_quantity !== null && $this->min_quantity < 0){
            $this->validationErrors['min_quantity'] = 'La quantité minimum doit être supérieure ou égale à 0.';
        }

        if($this->max_quantity !== null && $this->max_quantity < 0){
            $this->validationErrors['max_quantity'] = 'La quantité maximum doit être supérieure ou égale à 0.';
        }

        if($this->min_quantity !== null && $this->max_quantity !== null && $this->min_quantity > $this->max_quantity){
            $this->validationErrors['quantity_range'] = 'La quantité minimum ne peut pas être supérieure à la quantité maximum.';
        }

        return empty($this->validationErrors);
    }

    public function toArray(): array
    {
        return [
            'type'             => $this->getType()->value,
            'min_quantity'     => $this->min_quantity,
            'max_quantity'     => $this->max_quantity,
            'discount_percent' => $this->discount_percent,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            min_quantity: $data['min_quantity'] ?? null,
            max_quantity: $data['max_quantity'] ?? null,
            discount_percent: $data['discount_percent'] ?? null,
        );
    }

    public function getType(): DiscountType
    {
        return DiscountType::Quantity;
    }
}
