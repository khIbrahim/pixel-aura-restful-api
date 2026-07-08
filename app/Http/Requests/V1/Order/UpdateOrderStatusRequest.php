<?php

namespace App\Http\Requests\V1\Order;

use App\Enum\V1\Order\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ];
    }

    public function status(): OrderStatus
    {
        return OrderStatus::from($this->validated('status'));
    }

}
