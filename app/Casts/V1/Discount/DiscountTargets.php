<?php

namespace App\Casts\V1\Discount;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class DiscountTargets implements Castable, Arrayable
{

    public function __construct(
        public array $applicable_items      = [],
        public array $applicable_categories = [],
        public array $excluded_items        = [],
        public array $excluded_categories   = [],
    ){}

    public function toArray(): array
    {
        return [
            'applicable_items'      => $this->applicable_items,
            'applicable_categories' => $this->applicable_categories,
            'excluded_items'        => $this->excluded_items,
            'excluded_categories'   => $this->excluded_categories,
        ];
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {

            public function get(Model $model, string $key, mixed $value, array $attributes): ?DiscountTargets
            {
                if($value === null){
                    return null;
                }

                $data = json_decode($value, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return null;
                }

                return new DiscountTargets(
                    applicable_items: (array) ($data['applicable_items'] ?? []),
                    applicable_categories: (array) ($data['applicable_categories'] ?? []),
                    excluded_items: (array) ($data['excluded_items'] ?? []),
                    excluded_categories: (array) ($data['excluded_categories'] ?? []),
                );
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): false|string|null|static
            {
                if($value === null){
                    return null;
                }

                if(is_array($value)){
                    $value = DiscountTargets::fromArray($value);
                }

                if(! $value instanceof DiscountTargets){
                    throw new InvalidArgumentException('"value" doit être une instance de ' . DiscountTargets::class);
                }

                return json_encode($value->toArray());
            }
        };
    }

    public static function fromArray(array $data): static
    {
        return new self(
            applicable_items: (array) ($data['applicable_items'] ?? []),
            applicable_categories: (array) ($data['applicable_categories'] ?? []),
            excluded_items: (array) ($data['excluded_items'] ?? []),
            excluded_categories: (array) ($data['excluded_categories'] ?? []),
        );
    }
}
