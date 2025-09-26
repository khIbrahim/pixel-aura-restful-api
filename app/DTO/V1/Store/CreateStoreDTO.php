<?php

namespace App\DTO\V1\Store;

use App\DTO\V1\Abstract\BaseDTO;

final readonly class CreateStoreDTO extends BaseDTO
{

    public function __construct(
        public string  $name,
        public string  $sku,
        public string  $email,
        public ?string $phone            = null,
        public ?string $address          = null,
        public ?string $city             = null,
        public ?string $country          = null,
        public ?string $postal_code      = null,
        public ?string $currency         = null,
        public ?string $language         = null,
        public ?string $timezone         = null,
        public bool    $tax_inclusive    = false,
        public array   $receipt_settings = [],
        public array   $settings         = [],
        public ?array  $owner            = null,

    ){}

    public function toArray(): array
    {
        return [
            'name'             => $this->name,
            'sku'              => $this->sku,
            'email'            => $this->email,
            'phone'            => $this->phone,
            'address'          => $this->address,
            'city'             => $this->city,
            'country'          => $this->country,
            'postal_code'      => $this->postal_code,
            'currency'         => $this->currency,
            'language'         => $this->language,
            'timezone'         => $this->timezone,
            'tax_inclusive'    => $this->tax_inclusive,
            'receipt_settings' => $this->receipt_settings,
            'settings'         => $this->settings,
            'owner'            => $this->owner,
        ];
    }
}
