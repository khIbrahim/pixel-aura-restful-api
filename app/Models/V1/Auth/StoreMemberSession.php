<?php

namespace App\Models\V1\Auth;

use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int           $id
 * @property int           $store_member_id
 * @property int           $device_id
 * @property string        $session_id
 * @property string        $token_id
 * @property string        $ip_address
 * @property string        $user_agent
 * @property array         $metadata
 * @property boolean       $is_active
 * @property Carbon|null   $last_activity_at
 * @property Carbon|null   $expires_at
 * @property Carbon|null   $revoked_at
 * @property string|null   $revocation_reason
 * @property StoreMember   $storeMember
 * @property Device        $device
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 */
class StoreMemberSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_member_id',
        'device_id',
        'session_id',
        'token_id',
        'ip_address',
        'user_agent',
        'metadata',
        'is_active',
        'last_activity_at',
        'expires_at',
        'revoked_at',
        'revocation_reason',
    ];

    protected $casts = [
        'metadata'         => 'array',
        'is_active'        => 'boolean',
        'last_activity_at' => 'datetime',
        'expires_at'       => 'datetime',
        'revoked_at'       => 'datetime',
    ];

    /**
     * Relation avec le membre du magasin.
     */
    public function storeMember(): BelongsTo
    {
        return $this->belongsTo(StoreMember::class);
    }

    /**
     * Relation avec l'appareil.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Déterminer si la session est active.
     */
    public function isActive(): bool
    {
        return $this->is_active &&
               !$this->isExpired() &&
               !$this->isRevoked();
    }

    /**
     * Déterminer si la session a expiré.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Déterminer si la session a été révoquée.
     */
    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * Révoquer cette session.
     */
    public function revoke(string $reason = null): self
    {
        $this->update([
            'is_active'         => false,
            'revoked_at'        => now(),
            'revocation_reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Mettre à jour le timestamp de dernière activité.
     */
    public function touch(): bool
    {
        return $this->update([
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Prolonger la durée de vie de la session.
     */
    public function extend(int $minutes = 480): self
    {
        $this->update([
            'expires_at' => now()->addMinutes($minutes),
        ]);

        return $this;
    }

    /**
     * Scope pour récupérer les sessions actives.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereNull('revoked_at');
    }

    /**
     * Scope pour récupérer les sessions expirées.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope pour récupérer les sessions révoquées.
     */
    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Scope pour récupérer les sessions inactives.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false)
            ->orWhere(function ($query) {
                $query->whereNotNull('revoked_at')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('expires_at')
                            ->where('expires_at', '<', now());
                    });
            });
    }
}
