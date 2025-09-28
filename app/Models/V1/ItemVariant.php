<?php

namespace App\Models\V1;

use App\Traits\V1\Media\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int $id Identifiant unique de la variante
 * @property int $item_id Identifiant de l'item parent
 * @property string $name Nom de la variante (ex: "Rouge - Grande")
 * @property string $description Description détaillée de la variante
 * @property string $sku Code de référence unique pour la variante (Stock Keeping Unit)
 * @property int $price_cents Prix de la variante en centimes (inclut les taxes)
 * @property bool $is_active Indique si la variante est active et disponible à la vente
 * @property int $store_id Identifiant du magasin auquel appartient la variante
 * @property Item $item L'item parent auquel appartient cette variante
 * @property Store $store Le magasin auquel appartient cette variante
 */
class ItemVariant extends Model implements HasMedia
{
    use HasImages;

    protected $table = 'item_variants';

    protected $fillable = [
        'id',
        'item_id',
        'store_id',
        'name',
        'description',
        'sku',
        'price_cents',
        'is_active',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'is_active' => 'boolean',
        'item_id' => 'integer',
        'store_id' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
