<?php

namespace App\ValueObjects\V1\DiscountConfig;

use App\Contracts\V1\Discount\Config\DiscountConfigInterface;
use App\Enum\V1\Discount\BuyXGetYType;
use App\Enum\V1\Discount\DiscountType;
use App\Traits\V1\DiscountConfig\DiscountConfigTrait;

readonly class BuyXGetYConfig implements DiscountConfigInterface
{
    use DiscountConfigTrait;

    public function __construct(
        public int          $buy,
        public int          $get,
        public BuyXGetYType $apply_type = BuyXGetYType::Cheapest,
    ){}

    public function validate(): bool
    {
        $this->validationErrors = [];

        if ($this->buy <= 0) {
            $this->validationErrors['buy'] = "Le nombre d'articles à acheter doit être > 0";
        }

        if ($this->get <= 0) {
            $this->validationErrors['get'] = "Le nombre d'articles offerts doit être > 0";
        }

        return empty($this->validationErrors);
    }

    public function toArray(): array
    {
        return [
            'type'       => DiscountType::BuyXGetYFree->value,
            'buy'        => $this->buy,
            'get'        => $this->get,
            'apply_type' => $this->apply_type->value,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            buy: (int) $data['buy'],
            get: (int) $data['get'],
            apply_type: isset($data['apply_type']) ? BuyXGetYType::from($data['apply_type']) : BuyXGetYType::Cheapest,
        );
    }

    public function getType(): DiscountType
    {
        return DiscountType::BuyXGetYFree;
    }
}
