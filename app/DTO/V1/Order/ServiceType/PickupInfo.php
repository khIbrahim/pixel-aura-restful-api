<?php

namespace App\DTO\V1\Order\ServiceType;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final readonly class PickupInfo implements Castable, Arrayable
{

    public function __construct(
        public string  $contact_name,
    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            contact_name: (string) $data['contact_name'],
        );
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {

            public function get(Model $model, string $key, mixed $value, array $attributes): ?PickupInfo
            {
                if($value === null){
                    return null;
                }

                $data = json_decode($value, true);
                return PickupInfo::fromArray($data);
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): false|string|null
            {
                if($value === null){
                    return null;
                }

                if(is_array($value)){
                    $value = PickupInfo::fromArray($value);
                }

                if(! $value instanceof PickupInfo){
                    throw new InvalidArgumentException('"value" doit être une instance de ' . PickupInfo::class);
                }

                return json_encode($value->toArray());
            }
        };
    }

    public function toArray(): array
    {
        return [
            'contact_name' => $this->contact_name,
        ];
    }

}
