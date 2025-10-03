<?php

namespace App\DTO\V1\Order;

use App\Enum\V1\Order\OrderIngredientAction;
use App\Models\V1\Ingredient;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

final class OrderIngredientData implements Castable, Arrayable
{

    public function __construct(
        public int                   $ingredient_id,
        public OrderIngredientAction $action,
        public ?int                  $quantity   = null,
        public ?Ingredient           $ingredient = null
    ){}

    public function setIngredient(Ingredient $ingredient): self
    {
        $clone = clone $this;
        $clone->ingredient = $ingredient;

        return $clone;
    }

    public function getTotalPriceCents(): int
    {
        return $this->action === OrderIngredientAction::Add ? $this->ingredient->cost_per_unit_cents * $this->quantity : 0;
    }

    public function toArray(): array
    {
        return [
            'ingredient_id'     => $this->ingredient_id,
            'name'              => $this->ingredient?->name ?? null,
            'description'       => $this->ingredient?->description ?? null,
            'action'            => $this->action->value,
            'quantity'          => $this->quantity,
            'unit_price'        => $this->ingredient?->cost_per_unit_cents ?? null,
            'total_price_cents' => $this->ingredient !== null ? $this->getTotalPriceCents() : null
        ];
    }

    public static function fromArray(array $data): self
    {
        $ingredientId = (int) $data['id'];
        $action       = OrderIngredientAction::from((string) $data['action']);
        $quantity     = $action === OrderIngredientAction::Add ? (int) ($data['quantity'] ?? 1) : null;
        return new OrderIngredientData(
            ingredient_id: $ingredientId,
            action: $action,
            quantity: $quantity
        );
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes {
            public function get($model, string $key, $value, array $attributes): ?OrderIngredientData
            {
                if($value === null) {
                    return null;
                }

                $value = json_decode($value, true);
                return OrderIngredientData::fromArray($value);
            }

            public function set($model, string $key, $value, array $attributes): null|false|string
            {
                if($value === null){
                    return null;
                }

                if(is_array($value)){
                    return self::fromArray($value);
                }

                if (! $value instanceof OrderIngredientData) {
                    throw new InvalidArgumentException("'value' doit être une instance de " . OrderIngredientData::class);
                }

                return json_encode($value->toArray());
            }
        };
    }
}
