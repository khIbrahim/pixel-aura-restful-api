<?php

namespace App\Rules\V1;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Symfony\Component\Intl\Currencies;

class CurrencyRule implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(! Currencies::exists(strtoupper($value))){
            $fail("L'attribut :$attribute doit être valide au format ISO 4217 currency code. (ex: USD)");
        }
    }

}
