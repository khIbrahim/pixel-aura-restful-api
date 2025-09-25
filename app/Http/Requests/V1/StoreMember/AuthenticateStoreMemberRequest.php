<?php

namespace App\Http\Requests\V1\StoreMember;

use Illuminate\Foundation\Http\FormRequest;

class AuthenticateStoreMemberRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'code' => ['bail', 'required', 'string', 'max:20'],
            'pin'  => ['bail', 'required', 'string', 'max:8']
        ];
    }
}
