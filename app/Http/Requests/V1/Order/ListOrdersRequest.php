<?php

namespace App\Http\Requests\V1\Order;

use App\Enum\V1\Order\OrderChannel;
use App\Enum\V1\Order\OrderServiceType;
use App\Enum\V1\Order\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'       => ['nullable', 'string', 'max:255'],
            'statuses'     => ['nullable', 'array'],
            'statuses.*'   => [Rule::enum(OrderStatus::class)],

            'channel'      => ['nullable', Rule::enum(OrderChannel::class)],
            'service_type' => ['nullable', Rule::enum(OrderServiceType::class)],

            'date_from'    => ['nullable', 'date_format:Y-m-d'],
            'date_to'      => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],

            'sort'         => ['nullable', Rule::in(['newest', 'oldest', 'total_desc', 'total_asc'])],
            'page'         => ['nullable', 'integer', 'min:1'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
