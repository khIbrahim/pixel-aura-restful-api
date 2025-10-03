<?php

namespace App\DTO\V1\Order\ServiceType;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final readonly class DineInInfo implements Castable, Arrayable
{

    public function __construct(
        public string $table_number,
        public int    $number_of_guests = 1,
        public ?int   $server_id = null,
    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            table_number: (string) $data['table_number'],
            number_of_guests: array_key_exists('number_of_guests', $data) ? (int) $data['number_of_guests'] : 1,
            server_id: array_key_exists('server_id', $data) ? (int) $data['server_id'] : null,
        );
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {

            public function get(Model $model, string $key, mixed $value, array $attributes): ?DineInInfo
            {
                if($value === null){
                    return null;
                }

                $data = json_decode($value, true);
                return DineInInfo::fromArray($data);
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): false|string|null
            {
                if($value === null){
                    return null;
                }

                if(is_array($value)){
                    $value = DineInInfo::fromArray($value);
                }

                if(! $value instanceof DineInInfo){
                    throw new InvalidArgumentException('"value" doit être une instance de ' . DineInInfo::class);
                }

                return json_encode($value->toArray());
            }
        };
    }

    public function toArray(): array
    {
        return [
            'table_number'     => $this->table_number,
            'number_of_guests' => $this->number_of_guests,
            'server_id'        => $this->server_id,
        ];
    }
}
