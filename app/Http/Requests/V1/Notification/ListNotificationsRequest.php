<?php

namespace App\Http\Requests\V1\Notification;

use Illuminate\Foundation\Http\FormRequest;

class ListNotificationsRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:25'],
            'unread'   => ['nullable', 'boolean'],
        ];
    }

}
