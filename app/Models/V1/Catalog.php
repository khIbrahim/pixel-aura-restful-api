<?php

namespace App\Models\V1;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

/**
 * @property int         $id
 * @property int         $store_id
 * @property string      $name
 * @property bool        $is_draft
 * @property int         $version_int
 * @property array|null  $data
 * @property Carbon|null $published_at
 * @property string|null $idempotency_key
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class Catalog extends Model
{
    use HasFactory;

    protected $table = 'catalogs';

    protected $fillable = [
        'store_id',
        'name',
        'is_draft',
        'version_int',
        'data',
        'published_at',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'is_draft' => 'boolean',
            'version_int' => 'integer',
            'data' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_draft', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_draft', false)
            ->whereNotNull('published_at');
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('catalogs')
                ->where('is_draft', false)
                ->whereNotNull('published_at')
                ->groupBy('store_id');
        });
    }


    public function publish(): self
    {
        $latestVersion = self::where('store_id', $this->store_id)
            ->published()
            ->max('version_int') ?? 0;

        $this->version_int = $latestVersion + 1;
        $this->is_draft = false;
        $this->published_at = now();
        $this->save();

        $this->compileAndCacheMenu();

        return $this;
    }


    protected function compileAndCacheMenu(): void
    {
        $categories = Category::with(['items' => function ($query) {
            $query->where('is_active', true)
                ->with(['tax', 'modifierGroups']);
        }])
        ->where('store_id', $this->store_id)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

        $compiledMenu = [
            'version' => $this->version_int,
            'published_at' => $this->published_at->toIso8601String(),
            'name' => $this->name,
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image' => $category->getFirstMediaUrl('thumbnail'),
                    'items' => $category->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'price' => $item->base_price_cents,
                            'image' => $item->getFirstMediaUrl('thumbnail'),
                            'tax_rate' => $item->tax ? $item->tax->rate : null,
                            'tax_inclusive' => $item->tax ? $item->tax->inclusive : false,
                            'modifiers' => $item->modifierGroups->map(function ($group) {
                                return [
                                    'group_id' => $group->id,
                                    'name' => $group->name,
                                    'required' => (bool) $group->pivot->required,
                                    'min_select' => $group->pivot->min_select,
                                    'max_select' => $group->pivot->max_select,
                                    'options' => $group->modifiers->map(function ($modifier) {
                                        return [
                                            'id' => $modifier->id,
                                            'name' => $modifier->name,
                                            'price' => $modifier->price_cents,
                                        ];
                                    }),
                                ];
                            }),
                        ];
                    }),
                ];
            }),
        ];

        $cacheKey = "store:{$this->store_id}:menu:ver:{$this->version_int}";
        Redis::set($cacheKey, json_encode($compiledMenu));

        Redis::set("store:{$this->store_id}:latest_menu_version", $this->version_int);
    }

    public static function getCachedMenu(int $storeId, ?int $version = null): ?array
    {
        if ($version === null) {
            $version = Redis::get("store:{$storeId}:latest_menu_version");
            if (! $version) {
                return null;
            }
        }

        $cacheKey = "store:{$storeId}:menu:ver:{$version}";
        $menu = Redis::get($cacheKey);

        return $menu ? json_decode($menu, true) : null;
    }

    public static function generateIdempotencyKey(): string
    {
        return Str::uuid()->toString();
    }

    public static function createDraft(int $storeId, string $name, ?self $fromCatalog = null): self
    {
        $catalog = new self();
        $catalog->store_id = $storeId;
        $catalog->name = $name;
        $catalog->is_draft = true;
        $catalog->version_int = 0;

        if ($fromCatalog) {
            $catalog->data = $fromCatalog->data;
        }

        $catalog->save();
        return $catalog;
    }

    public function getEtagAttribute(): string
    {
        return 'W/"ver-' . $this->version_int . '"';
    }
}
