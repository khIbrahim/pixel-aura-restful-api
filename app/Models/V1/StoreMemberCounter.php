<?php

namespace App\Models\V1;

use App\Enum\StoreMemberRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int             $store_id
 * @property StoreMemberRole $role
 * @property int             $next_code
 */
class StoreMemberCounter extends Model
{
    use HasFactory;

    protected $table = 'store_member_counters';

    protected $fillable = [
        'store_id',
        'role',
        'next_code',
    ];

    protected $casts = [
        'role' => StoreMemberRole::class
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

}
