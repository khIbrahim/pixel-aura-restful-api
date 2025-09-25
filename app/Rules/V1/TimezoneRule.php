<?php

namespace App\Rules\V1;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Symfony\Component\Intl\Timezones;

class TimezoneRule implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Timezones::exists($value)){
            $fail("L'attribut :$attribute doit être un timezone valide, ex: Africa/Algiers.");
        }
    }
}
