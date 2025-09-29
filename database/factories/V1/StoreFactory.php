<?php

namespace Database\Factories\V1;

use App\Contracts\V1\Shared\SkuGeneratorServiceInterface;
use App\Models\V1\Store;
use App\Models\V1\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Timezones;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->company;

        return [
            'name'            => $name,
            'sku'             => app(SkuGeneratorServiceInterface::class)->generateSku($name, -1),
            'owner_id'        => User::factory(),
            'phone'           => $this->faker->phoneNumber,
            'email'           => $this->faker->unique()->safeEmail,
            'address'         => $this->faker->streetAddress,
            'city'            => $this->faker->city,
            'country'         => $this->faker->country,
            'postal_code'     => $this->faker->postcode,
            'currency'        => array_keys(Currencies::getCurrencyCodes())[array_rand(array_keys(Currencies::getCurrencyCodes()))],
            'language'        => array_keys(Languages::getNames())[array_rand(array_keys(Languages::getNames()))],
            'timezone'        => array_keys(Timezones::getNames())[array_rand(array_keys(Timezones::getNames()))],
            'tax_inclusive'   => mt_rand(0, 1) === 1,
            'default_vat_rate'=> (float) mt_rand(1, 99) / 100,
            'receipt_settings'=> null,
            'settings'        => null,
            'menu_version'    => mt_rand(1, 2),
            'is_active'       => true,
        ];
    }
}
