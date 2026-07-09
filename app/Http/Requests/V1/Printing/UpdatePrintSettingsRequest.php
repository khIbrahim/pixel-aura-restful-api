<?php

namespace App\Http\Requests\V1\Printing;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrintSettingsRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_printer_name'                => ['nullable', 'string', 'max:255'],
            'receipt_printer_name'                 => ['nullable', 'string', 'max:255'],
            'kitchen_printer_name'                 => ['nullable', 'string', 'max:255'],
            'auto_print_customer_on_order_created' => ['boolean'],
            'auto_print_receipt_on_order_created'  => ['boolean'],
            'auto_print_kitchen_on_preparing'      => ['boolean'],
        ];
    }
}
