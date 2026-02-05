<?php

namespace App\Casts\V1\Discount;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class DiscountConditions implements Castable, Arrayable
{

    public function __construct(
        public ?int $min_order_amount_cents = null,
        public ?int $min_items_quantity     = null,
        public ?int $min_customer_orders    = null,
        public ?int $max_order_amount_cents = null,
    ){}

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {

            public function get(Model $model, string $key, mixed $value, array $attributes): ?DiscountConditions
            {
                if ($value === null){
                    return null;
                }

                $data = is_array($value) ? $value : json_decode($value, true);
                return DiscountConditions::fromArray($data);
            }

            public function set(Model $model, string $key, mixed $value, array $attributes)
            {
                if($value === null){
                    return null;
                }

                if(is_array($value)){
                    $value = DiscountConditions::fromArray($value);
                }

                if(! $value instanceof DiscountConditions){
                    throw new InvalidArgumentException("'value' doit être une instance de " . DiscountConditions::class . " ou un tableau.");
                }
            }
        };
    }

    public static function fromArray(array $data): static
    {
        return new self(
            min_order_amount_cents: $data['min_order_amount_cents'] ?? null,
            min_items_quantity: $data['min_items_quantity'] ?? null,
            min_customer_orders: $data['min_customer_orders'] ?? null,
            max_order_amount_cents: $data['max_order_amount_cents'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'min_order_amount_cents' => $this->min_order_amount_cents,
            'min_items_quantity'     => $this->min_items_quantity,
            'min_customer_orders'    => $this->min_customer_orders,
            'max_order_amount_cents' => $this->max_order_amount_cents,
        ];
    }
}
