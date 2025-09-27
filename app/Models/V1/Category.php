<?php

namespace App\Models\V1;

use App\Contracts\V1\Media\DefinesMediaPath;
use App\Traits\V1\Media\HasImages;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nette\Utils\FileSystem;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int           $id
 * @property int           $store_id
 * @property string        $name
 * @property string|null   $description
 * @property array|null    $tags
 * @property string        $slug
 * @property int           $position
 * @property null|int      $parent_id
 * @property boolean       $is_active
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property Category|null $parent
 * @property Store         $store
 * @property Category[]    $children
 */
class Category extends Model implements HasMedia, DefinesMediaPath
{
    use HasImages;

    protected $fillable = [
        'store_id',
        'name',
        'slug',
        'description',
        'tags',
        'position',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tags'      => 'array',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('position');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function getMediaBasePath(): string
    {
        return FileSystem::joinPaths(
            'stores',
            $this->store_id,
            'categories',
            $this->id . '-' . $this->slug
        ) . '/';
    }

}
