<?php

namespace App\Models\V1;

use App\Enum\StoreMemberRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

/**
 * @property int             $id
 * @property int             $store_id
 * @property int|null        $user_id
 * @property string          $pin_hash
 * @property string          $name
 * @property int             $code_number
 * @property bool            $is_active
 * @property StoreMemberRole $role
 * @property array           $meta
 * @property array           $permissions
 * @property Carbon          $pin_last_changed_at
 * @property int             $login_count
 * @property int             $failed_attempts
 * @property Carbon|null     $locked_until
 * @property Store           $store
 *
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 */
class StoreMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'code_number',
        'role',
        'pin_hash',
        'pin_last_changed_at',
        'last_login_at',
        'login_count',
        'failed_attempts',
        'locked_until',
        'is_active',
        'meta',
        'permissions'
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'meta'                => 'array',
        'permissions'         => 'array',
        'pin_last_changed_at' => 'datetime',
        'last_login_at'       => 'datetime',
        'role'                => StoreMemberRole::class,
        'locked_until'        => 'datetime',
    ];

    protected $hidden = ['pin_hash'];

    /**
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(StoreMemberCounter::class, 'store_id', 'store_id')
            ->where('role', $this->role->value);
    }

    public function checkPin(string $hashPin): bool
    {
        return Hash::check($hashPin, $this->pin_hash);
    }

    // sous forme EMP-001, etc.
    public function checkCode(string $code): bool
    {
        return $this->code() === $code;
    }

    public function code(): string
    {
        $role = $this->getAttribute('role');
        if (! $role instanceof StoreMemberRole) {
            $role = StoreMemberRole::from($role);
        }

        $prefix = $role->getPrefix();

        return $prefix . "-" . str_pad((string) $this->code_number, 3, '0', STR_PAD_LEFT);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }

    public function hasPermission(string $ability): bool
    {
        $permissions = $this->getPermissions();
        return in_array('*', $permissions) || in_array($ability, $permissions);
    }

}
