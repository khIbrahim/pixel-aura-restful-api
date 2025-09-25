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
 * @property string      $slug
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
 * @property bool        $tax_inclusive
 * @property float       $default_vat_rate
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
        'slug',
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
        'tax_inclusive',
        'default_vat_rate',
        'receipt_settings',
        'settings',
        'menu_version',
        'is_active',
    ];

    protected $casts = [
        'receipt_settings' => 'array',
        'settings'         => 'array',
        'is_active'        => 'boolean',
        'tax_inclusive'    => 'boolean',
        'default_vat_rate' => 'decimal:2',
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

    public function catalogs(): HasMany
    {
        return $this->hasMany(Catalog::class);
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
