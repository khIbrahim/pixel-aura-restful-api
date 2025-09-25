<?php

namespace App\Models\V1\Auth;

use App\Enum\DeviceType;
use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use Carbon\Carbon;
use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

/**
 * @property int         $id
 * @property array       $abilities
 * @property int         $store_id
 * @property int         $fingerprint
 * @property int         $device_id
 * @property null|int    $store_member_id
 * @property null|Carbon $expires_at
 * @property null|Carbon $last_used_at
 * @property null|Carbon $created_at
 * @property bool        $revoked
 * @property Device      $tokenable
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
        'device_id',
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

}
