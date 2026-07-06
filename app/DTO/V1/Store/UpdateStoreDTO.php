<?php

namespace App\DTO\V1\Store;

use App\DTO\V1\Abstract\BaseDTO;

final readonly class UpdateStoreDTO extends BaseDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $sku = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?string $postal_code = null,
        public ?string $currency = null,
        public ?string $language = null,
        public ?string $timezone = null,
        public ?array $receipt_settings = null,
        public ?array $settings = null,
        public ?bool $is_active = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'sku' => $this->sku,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'currency' => $this->currency,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'receipt_settings' => $this->receipt_settings,
            'settings' => $this->settings,
            'is_active' => $this->is_active,
        ], static fn ($value) => $value !== null);
    }
}
