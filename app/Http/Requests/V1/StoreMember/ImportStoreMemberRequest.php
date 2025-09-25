<?php

namespace App\Http\Requests\V1\StoreMember;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ImportStoreMemberRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'bail',
                'required',
                File::types(['csv', 'xlsx', 'xls'])
                    ->max(10 * 1024), // 10MB
            ],
            'async' => [
                'sometimes',
                'boolean',
            ],
            'options' => [
                'sometimes',
                'array',
            ],
            'options.batch_size' => [
                'sometimes',
                'integer',
                'min:1',
                'max:1000',
            ],
            'options.use_batch_mode' => [
                'sometimes',
                'boolean',
            ],
            'options.priority' => [
                'sometimes',
                'integer',
                'min:0',
                'max:10',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'import_file.required'   => 'Le fichier d\'importation est obligatoire.',
            'import_file.file'       => 'Le fichier d\'importation est invalide.',
            'import_file.mimes'      => 'Le fichier d\'importation doit être au format CSV, XLSX ou XLS.',
            'import_file.max'        => 'Le fichier d\'importation ne doit pas dépasser 10 Mo.',
            'options.batch_size.min' => 'La taille du lot doit être au minimum 1.',
            'options.batch_size.max' => 'La taille du lot ne doit pas dépasser 1000.',
            'options.priority.min'   => 'La priorité doit être au minimum 0.',
            'options.priority.max'   => 'La priorité ne doit pas dépasser 10.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('async') && is_string($this->async)) {
            $this->merge([
                'async' => $this->async === '1' || strtolower($this->async) === 'true',
            ]);
        }

        if ($this->has('options') && is_string($this->options)) {
            try {
                $options = json_decode($this->options, true, 512, JSON_THROW_ON_ERROR);
                $this->merge(['options' => $options]);
            } catch (\JsonException $e) {

            }
        }
    }
}
