<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $id Identifiant unique de la variante
 * @property int    $item_id Identifiant de l'item parent
 * @property string $name Nom de la variante (ex: "Rouge - Grande")
 * @property string $sku Code de référence unique pour la variante (Stock Keeping Unit)
 * @property int    $price_cents Prix de la variante en centimes (inclut les taxes)
 * @property bool   $is_active Indique si la variante est active et disponible à la vente
 */
class ItemVariant extends Model
{
    protected $table = 'item_variants';

    protected $fillable = [
        'id',
        'item_id',
        'name',
        'sku',
        'price_cents',
        'is_active'
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'is_active'   => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
