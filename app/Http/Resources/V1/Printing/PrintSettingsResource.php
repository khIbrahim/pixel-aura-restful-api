<?php

namespace App\Http\Resources\V1\Printing;

use App\Models\V1\StorePrintSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StorePrintSettings
 */
class PrintSettingsResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'                                   => $this->id,
            'store_id'                             => $this->store_id,
            'customer_printer_name'                => $this->customer_printer_name,
            'receipt_printer_name'                 => $this->receipt_printer_name,
            'kitchen_printer_name'                 => $this->kitchen_printer_name,
            'auto_print_customer_on_order_created' => $this->auto_print_customer_on_order_created,
            'auto_print_receipt_on_order_created'  => $this->auto_print_receipt_on_order_created,
            'auto_print_kitchen_on_preparing'      => $this->auto_print_kitchen_on_preparing,
            'created_at'                           => $this->created_at?->toISOString(),
            'updated_at'                           => $this->updated_at?->toISOString(),
        ];
    }
}
