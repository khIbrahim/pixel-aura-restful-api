<?php

namespace App\Rules\V1;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class ImageUrlRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => ['required', 'url']]
        );

        if ($validator->fails()) {
            $fail("L'URL fournie n'est pas une URL valide.");
            return;
        }

        $parsedUrl = parse_url($value);
        if (! in_array($parsedUrl['scheme'] ?? '', ['http', 'https'])) {
            $fail("L'URL fournie doit commencer par http:// ou https://");
        }
    }
}
