<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int              $id
 * @property int              $order_id
 * @property int              $item_id
 * @property string|null      $item_name
 * @property string|null      $item_description
 * @property string|null      $item_image_url
 * @property string|null      $item_sku
 * @property Item             $item
 * @property null|int         $variant_id
 * @property string|null      $variant_name
 * @property null|ItemVariant $variant
 * @property array            $selected_options
 * @property array            $ingredient_modifications
 * @property int              $options_price_cents
 * @property int              $ingredients_price_cents
 * @property int              $base_price_cents
 * @property int              $item_total_cents
 * @property int              $quantity
 * @property int              $final_total_cents
 * @property null|string      $special_instructions
 * @property Order            $order
 */
class OrderItem extends Model
{

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'item_id',
        'item_name',
        'item_description',
        'item_image_url',
        'item_sku',
        'variant_id',
        'variant_name',
        'selected_options',
        'ingredient_modifications',
        'options_price_cents',
        'ingredients_price_cents',
        'base_price_cents',
        'item_total_cents',
        'quantity',
        'final_total_cents',
        'special_instructions'
    ];

    protected $casts = [
        'selected_options'         => 'array',
        'ingredient_modifications' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class, 'variant_id');
    }

    public function getPreparationTime(): int
    {
        $baseTime = $this->item->preparation_time_minutes ?? 0;

        $optionsTime = 0;
        if ($this->selected_options && is_array($this->selected_options)) {
            foreach ($this->selected_options as $option) {
                if (isset($option['preparation_time_minutes'])) {
                    $optionsTime += (int) $option['preparation_time_minutes'];
                }
            }
        }

        return $baseTime + $optionsTime;
    }
}
