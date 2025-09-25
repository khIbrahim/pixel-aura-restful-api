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
 * @property int         $id Identifiant unique de l'option
 * @property int         $store_id Identifiant du magasin auquel l'option appartient
 * @property string      $name Nom de l'option
 * @property null|string $description Description de l'option
 * @property int         $price_cents Prix de l'option en centimes
 * @property bool        $is_active Indique si l'option est active et disponible
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'is_active'   => 'boolean',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_options')
            ->withPivot(['price_cents', 'is_active', 'name', 'description'])
            ->withTimestamps();
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(OptionList::class, 'option_list_id');
    }

    public function getMediaBasePath(): string
    {
        return FileSystem::joinPaths('stores', $this->store_id, 'options', $this->id . '-' . Str::trim($this->name)) . '/';
    }
}
