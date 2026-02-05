<?php

namespace App\Models\V1\Auth;

use App\Models\V1\Device;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use Carbon\Carbon;
use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

/**
 * @property int         $id
 * @property array       $abilities
 * @property int         $store_id
 * @property int         $fingerprint
 * @property null|int    $store_member_id
 * @property null|Carbon $expires_at
 * @property null|Carbon $last_used_at
 * @property null|Carbon $created_at
 * @property bool        $revoked
 * @property Store       $tokenable
 * @property StoreMember $storeMember
 */
class PersonalAccessToken extends SanctumToken
{
    use Timestamp;

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'tokenable_id',
        'tokenable_type',
        'fingerprint',
        'store_id',
        'store_member_id',
        'last_used_at',
        'revoked'
    ];

    protected $casts = [
        'abilities'    => 'json',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
        'revoked'      => 'boolean',
    ];

    protected $hidden = [
        'token',
    ];

    public function storeMember(): BelongsTo
    {
        return $this->belongsTo(StoreMember::class);
    }

    public function tokenable(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'tokenable_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && Carbon::now()->greaterThan($this->expires_at);
    }

}
