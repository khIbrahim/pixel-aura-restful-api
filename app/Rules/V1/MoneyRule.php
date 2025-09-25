<?php

namespace App\Rules\V1;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MoneyRule implements ValidationRule
{

    public function __construct(
        private readonly bool $allowZero = false
    ){}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(! is_int($value)) {
            $fail("L'attribute '$attribute' doit être un entier représentant des centimes.");
            return;
        }

        if ($value < 0){
            $fail("L'attribute '$attribute' ne peut pas être négatif.");
            return;
        }

        if(! $this->allowZero && $value === 0){
            $fail("L'attribute '$attribute' doit être supérieur à zéro.");
        }
    }
}
