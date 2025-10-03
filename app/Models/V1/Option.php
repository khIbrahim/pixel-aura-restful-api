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
use Illuminate\Support\Str;
use Nette\Utils\FileSystem;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int         $id
 * @property int         $store_id
 * @property string      $name
 * @property null|string $description
 * @property int         $price_cents
 * @property bool        $is_active
 * @property int         $option_list_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Store       $store
 * @property OptionList  $list
 * @property int         $preparation_time_minutes
 */
class Option extends Model implements HasMedia, DefinesMediaPath
{
    use HasFactory, Timestamp, HasImages;

    protected $table = 'options';

    protected $fillable = [
        'name',
        'store_id',
        'description',
        'store_id',
        'price_cents',
        'is_active',
        'option_list_id',
        'preparation_time_minutes'
    ];

    protected $casts = [
        'price_cents'              => 'integer',
        'is_active'                => 'boolean',
        'store_id'                 => 'integer',
        'option_list_id'           => 'integer',
        'preparation_time_minutes' => 'integer',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_options')
            ->withPivot(['price_cents', 'is_active', 'name', 'description'])
            ->withTimestamps();
    }

    public function hasActiveItems(): bool
    {
        return $this->items()->wherePivot('is_active', true)->exists();
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(OptionList::class, 'option_list_id');
    }

    public function isInActiveOptionList(): bool
    {
        return $this->list()->where('is_active', false)->exists();
    }

    public function getMediaBasePath(): string
    {
        return FileSystem::joinPaths('stores', $this->store_id, 'options', $this->id . '-' . Str::trim($this->name)) . '/';
    }
}
