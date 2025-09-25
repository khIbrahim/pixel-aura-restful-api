<?php

namespace App\Rules\V1;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Symfony\Component\Intl\Languages;

class LanguageRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(! Languages::exists(strtolower($value))){
            $fail("L'attribut :$attribute doit être valide au format ISO 639-1 ou ISO 639-2 language code (ex: dz).");
        }
    }
}
