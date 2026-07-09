<?php

namespace App\Models\V1;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $store_id
 * @property null|string $customer_printer_name
 * @property null|string $receipt_printer_name
 * @property null|string $kitchen_printer_name
 * @property bool        $auto_print_customer_on_order_created
 * @property bool        $auto_print_receipt_on_order_created
 * @property bool        $auto_print_kitchen_on_preparing
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Store       $store
 */
class StorePrintSettings extends Model
{

    protected $table = 'store_print_settings';

    protected $fillable = [
        'store_id',
        'customer_printer_name',
        'receipt_printer_name',
        'kitchen_printer_name',
        'auto_print_customer_on_order_created',
        'auto_print_receipt_on_order_created',
        'auto_print_kitchen_on_preparing',
    ];

    protected $casts = [
        'auto_print_customer_on_order_created' => 'boolean',
        'auto_print_receipt_on_order_created'  => 'boolean',
        'auto_print_kitchen_on_preparing'      => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
