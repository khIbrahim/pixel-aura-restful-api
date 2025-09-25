<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class InventoryLevel extends Model
{
    protected $table = 'inventory_levels';

    protected $fillable = [
        'item_id',
        'location_id',
        'available_quantity',
        'last_restocked_at',
    ];

    protected $casts = [
        'available_quantity' => 'integer',
        'last_restocked_at' => 'datetime',
    ];
}
