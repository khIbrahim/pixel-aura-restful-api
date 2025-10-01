<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * @property int           $id
 * @property int           $store_id
 * @property string        $name
 * @property string|null   $description
 * @property string        $sku
 * @property int           $min_selections
 * @property int           $max_selections
 * @property bool          $is_active
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property Carbon|null   $deleted_at
 * @property Option[]|null $options
 * @property Item[]|null   $items
 * @property Store         $store
 */
class OptionList extends Model
{
//    use SoftDeletes;

    protected $table = "option_lists";

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'sku',
        'min_selections',
        'max_selections',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'min_selections' => 'integer',
        'max_selections' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class, 'option_list_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_option_lists')
            ->withPivot(['store_id', 'is_required', 'min_selections', 'max_selections', 'display_order', 'is_active'])
            ->withTimestamps();
    }


    public function scopeActive($query, bool $active = true)
    {
        return $query->where('is_active', $active);
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }


    public function isValidSelectionCount(int $count): bool
    {
        return $count >= $this->min_selections && ($this->max_selections === null || $count <= $this->max_selections);
    }
}
