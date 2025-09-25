<?php

namespace App\Http\Requests\V1\StoreMember;

use App\Enum\StoreMemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportStoreMembersRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'async'     => ['sometimes', 'boolean'],
            'format'    => ['sometimes', 'in:xlsx,csv,ods'],
            'role'      => ['sometimes', Rule::enum(StoreMemberRole::class)],
            'is_active' => ['sometimes', 'boolean'],
            'priority'  => ['sometimes', 'integer', 'min:1', 'max:10']
        ];
    }
}
