<?php

namespace App\Models\V1;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property string      $name
 * @property string      $sku
 * @property int         $owner_id
 * @property null|string $email
 * @property null|string $phone
 * @property null|string $address
 * @property null|string $city
 * @property null|string $country
 * @property null|string $postal_code
 * @property null|string $currency
 * @property null|string $language
 * @property null|string $timezone
 * @property array       $settings
 * @property int         $menu_version
 * @property bool        $is_active
 * @property User        $owner
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'owner_id',
        'phone',
        'email',
        'address',
        'city',
        'country',
        'postal_code',
        'currency',
        'language',
        'timezone',
        'receipt_settings',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'receipt_settings' => 'array',
        'settings'         => 'array',
        'is_active'        => 'boolean',
//        'phone'            => E164PhoneNumberCast::class . ':AUTO'
    ];

    public function storeMembers(): HasMany
    {
        return $this->hasMany(StoreMember::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    public function optionLists(): HasMany
    {
        return $this->hasMany(OptionList::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function active(): bool
    {
        return $this->is_active;
    }

}
