<?php

namespace App\Traits\V1\Model;

use App\ValueObjects\V1\Money;

trait HasMoneyAttributes
{

    public function getMoney(string $attribute, ?string $currency = null): Money
    {
        $currency = $currency ?? $this->currency ?? config('pos.currency', 'DZD');
        $amount   = $this->{$attribute} ?? 0;

        return Money::ofMinor($amount, $currency);
    }

    public function formatMoney(int $cents, ?string $locale = null, ?string $currency = null): string
    {
        $currency = $currency ?? $this->currency ?? config('pos.currency', 'DZD');
        $locale   = $locale ?? config('pos.locale', 'fr_DZ');

        return Money::ofMinor($cents, $currency)->formatted($locale);
    }

}
