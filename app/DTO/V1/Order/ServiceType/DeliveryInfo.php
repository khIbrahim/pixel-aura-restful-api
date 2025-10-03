<?php

namespace App\DTO\V1\Order\ServiceType;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final readonly class DeliveryInfo implements Castable, Arrayable
{

    public function __construct(
        public string  $address,
        public string  $contact_name,
        public string  $contact_phone,
        public ?string $notes = null,
        public int     $fee_cents = 0,
    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            address: (string) $data['address'],
            contact_name: (string) $data['contact_name'],
            contact_phone: (string) $data['contact_phone'],
            notes: array_key_exists('notes', $data) ? (string) $data['notes'] : null,
            fee_cents: array_key_exists('fee_cents', $data) ? (int) $data['fee_cents'] : 0,
        );
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {

            public function get(Model $model, string $key, mixed $value, array $attributes): ?DeliveryInfo
            {
                if($value === null){
                    return null;
                }

                $data = json_decode($value, true);
                return DeliveryInfo::fromArray($data);
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): false|string|null
            {
                if($value === null){
                    return null;
                }

                if(is_array($value)){
                    $value = DeliveryInfo::fromArray($value);
                }

                if(! $value instanceof DeliveryInfo){
                    throw new InvalidArgumentException('"value" doit être une instance de ' . DeliveryInfo::class);
                }

                return json_encode($value->toArray());
            }

        };
    }

    public function toArray(): array
    {
        return [
            'address'       => $this->address,
            'contact_name'  => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'notes'         => $this->notes,
            'fee_cents'     => $this->fee_cents,
        ];
    }
}
