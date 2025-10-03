<?php

namespace App\DTO\V1\Order;

use App\Models\V1\Option;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

final class OrderOptionData implements Castable, Arrayable
{

    public function __construct(
        public int $option_id,
        public int $quantity,
        public ?Option $option = null,
    ){}

    public function setOption(Option $option): self
    {
        $clone = clone $this;
        $clone->option = $option;
        return $clone;
    }

    public function getTotalPriceCents(): int
    {
        return $this->option->price_cents * $this->quantity;
    }

    public function toArray(): array
    {
        return [
            'option_id'         => $this->option_id,
            'quantity'          => $this->quantity,
            'name'              => $this->option?->name ?? null,
            'description'       => $this->option?->description ?? null,
            'unit_price'        => $this->option?->price_cents ?? null,
            'total_price_cents' => $this->option !== null ? $this->getTotalPriceCents() : null
        ];
    }

    public static function fromArray(array $data): self
    {
        $optionId = (int) $data['id'];
        $quantity = (int) ($data['quantity'] ?? 1);
        return new OrderOptionData(
            option_id: $optionId,
            quantity: $quantity
        );
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes {
            public function get($model, string $key, $value, array $attributes): ?OrderOptionData
            {
                if($value === null){
                    return null;
                }

                $data = json_decode($value, true);
                return OrderOptionData::fromArray($data);
            }

            public function set($model, string $key, $value, array $attributes): false|string|OrderOptionData|null
            {
                if($value === null){
                    return null;
                }

                if(is_array($value)){
                    return OrderOptionData::fromArray($value);
                }

                if (! $value instanceof OrderOptionData) {
                    throw new InvalidArgumentException("'value' doit être une instance de " . OrderOptionData::class);
                }

                return json_encode($value->toArray());
            }
        };
    }
}
