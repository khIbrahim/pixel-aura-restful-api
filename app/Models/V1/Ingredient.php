<?php

namespace App\Models\V1;

use App\Contracts\V1\Media\DefinesMediaPath;
use App\Traits\V1\Media\HasImages;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Nette\Utils\FileSystem;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int    $id
 * @property int    $store_id
 * @property string $name
 * @property string $description
 * @property bool   $is_allergen
 * @property bool   $is_mandatory
 * @property int    $cost_per_unit_cents
 * @property string $unit
 * @property bool   $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Ingredient extends Model implements HasMedia, DefinesMediaPath
{
    use HasFactory, HasImages;

    protected $table = 'ingredients';

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'is_allergen',
        'is_mandatory',
        'cost_per_unit_cents',
        'unit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_allergen'         => 'boolean',
            'cost_per_unit_cents' => 'integer',
            'is_active'           => 'boolean',
            'is_mandatory'        => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_ingredients')
            ->withPivot('id', 'store_id', 'is_active', 'name', 'description', 'cost_per_unit_cents', 'unit', 'is_mandatory', 'is_allergen')
            ->withTimestamps();
    }

    public function hasActiveItems(): bool
    {
        return $this->items()->where('is_active', true)->exists();
    }

    /**
     * Format cost per unit for display.
     */
    public function getCostPerUnitAttribute(): float
    {
        return $this->cost_per_unit_cents / 100;
    }

    /**
     * Set the cost per unit from a decimal value.
     */
    public function setCostPerUnitAttribute(float $value): void
    {
        $this->attributes['cost_per_unit_cents'] = (int) ($value * 100);
    }

    public function getMediaBasePath(): string
    {
        return FileSystem::joinPaths('stores', $this->store_id, 'ingredients', $this->id . '-' . Str::trim($this->name)) . '/';
    }
}
