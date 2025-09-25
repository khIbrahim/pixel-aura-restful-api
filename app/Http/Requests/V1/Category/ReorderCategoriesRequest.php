<?php

namespace App\Http\Requests\V1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'            => ['required','array','min:1'],
            'items.*.id'       => ['required','integer', Rule::exists('categories','id')->where(fn($q) => $q->where('store_id', $this->user()->store_id))],
            'items.*.position' => ['required','integer','min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function($v) {
            if (! $v->errors()->isEmpty()) {
                return;
            }
            $positions = array_map(fn($row) => $row['position'], $this->validated('items'));
            if (count($positions) !== count(array_unique($positions))) {
                $v->errors()->add('items', 'Les positions doivent être uniques.');
            }
        });
    }

    public function idPositionMap(): array
    {
        $map = [];
        foreach ($this->validated('items') as $row) {
            $map[(int)$row['id']] = (int)$row['position'];
        }
        return $map;
    }
}

