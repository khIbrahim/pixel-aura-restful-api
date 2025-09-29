<?php

namespace App\Models\V1;

use App\Contracts\V1\Media\DefinesMediaPath;
use App\Traits\V1\Media\HasImages;
use Carbon\Carbon;
use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nette\Utils\FileSystem;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int                $id
 * @property int                $store_id
 * @property int|null           $category_id
 * @property int|null           $tax_id
 * @property string             $name
 * @property string             $sku
 * @property string|null        $barcode
 * @property string|null        $description
 * @property string             $currency
 * @property int                $base_price_cents
 * @property int                $current_cost_cents
 * @property bool               $is_active
 * @property bool               $track_inventory
 * @property int                $stock
 * @property bool               $loyalty_eligible
 * @property int|null           $age_restriction
 * @property int|null           $reorder_level
 * @property int|null           $weight_grams
 * @property array|null         $tags
 * @property array|null         $metadata
 * @property int|null           $created_by
 * @property int|null           $updated_by
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 * @property Carbon|null        $deleted_at
 * @property Store              $store
 * @property Category|null      $category
 * @property Tax|null           $tax
 * @property StoreMember|null   $creator
 * @property StoreMember|null   $updater
 * @property ItemVariant[]|null $variants
 * @property Ingredient[]|null  $ingredients
 * @property Option[]|null      $options
 * @property OptionList[]|null  $optionLists
 */
class Item extends Model implements HasMedia, DefinesMediaPath
{
    use HasFactory, SoftDeletes, Timestamp, HasImages;

    protected $table = 'items';

    protected $fillable = [
        'store_id',
        'category_id',
        'tax_id',
        'name',
        'sku',
        'barcode',
        'description',
        'currency',
        'base_price_cents',
        'current_cost_cents',
        'is_active',
        'track_inventory',
        'stock',
        'loyalty_eligible',
        'age_restriction',
        'reorder_level',
        'weight_grams',
        'tags',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'base_price_cents'   => 'integer',
            'current_cost_cents' => 'integer',
            'is_active'          => 'boolean',
            'track_inventory'    => 'boolean',
            'stock'              => 'integer',
            'loyalty_eligible'   => 'boolean',
            'age_restriction'    => 'integer',
            'reorder_level'      => 'integer',
            'weight_grams'       => 'integer',
            'metadata'           => 'array',
            'created_at'         => 'datetime',
            'updated_at'         => 'datetime',
            'deleted_at'         => 'datetime',
            'tags'               => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(StoreMember::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(StoreMember::class, 'updated_by');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ItemVariant::class, 'item_id');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'item_ingredients')
            ->withPivot(['store_id', 'name', 'description', 'is_allergen', 'unit', 'is_mandatory', 'cost_per_unit_cents', 'is_active'])
            ->withTimestamps();
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'item_options')
            ->withPivot(['store_id', 'name', 'price_cents', 'is_active', 'description'])
            ->withTimestamps();
    }

    public function optionLists(): BelongsToMany
    {
        return $this->belongsToMany(OptionList::class, 'item_option_lists')
            ->withPivot(['store_id', 'is_required', 'min_selections', 'max_selections', 'display_order', 'is_active'])
            ->withTimestamps();
    }

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function getBasePriceAttribute(): float
    {
        return $this->base_price_cents / 100;
    }

    public function getCurrentCostAttribute(): float
    {
        return $this->current_cost_cents / 100;
    }

    public function setBasePriceAttribute(float $value): void
    {
        $this->attributes['base_price_cents'] = (int) ($value * 100);
    }

    public function setCurrentCostAttribute(float $value): void
    {
        $this->attributes['current_cost_cents'] = (int) ($value * 100);
    }

    public function getMediaBasePath(): string
    {
        return FileSystem::joinPaths('stores', $this->store_id, 'items', $this->id . '-' . $this->sku) . '/';
    }

}
