<?php

namespace App\Rules\V1;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Dimensions;
use Illuminate\Validation\Rules\File;

class ImageRule implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $config    = config('media-management.validation');
        $validator = Validator::make(
            [$attribute => $value],
            [
                $attribute => [
                    File::image()
                        ->max($config['max_size'])
                        ->dimensions(new Dimensions([
                            'minWidth'  => $config['dimensions']['min_width'],
                            'minHeight' => $config['dimensions']['min_height'],
                            'maxWidth'  => $config['dimensions']['max_width'],
                            'maxHeight' => $config['dimensions']['max_height'],
                        ])),
                ],
            ]
        );

        if($validator->fails()) {
            $fail($validator->errors()->first($attribute) ?? 'Le fichier doit être une image valide.');
        }
    }

}
