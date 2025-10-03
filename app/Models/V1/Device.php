<?php

namespace App\Models\V1;

use App\Enum\V1\DeviceType;
use Carbon\Carbon;
use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int            $id
 * @property string         $store_id
 * @property string         $name
 * @property DeviceType     $type
 * @property string         $fingerprint_hash
 * @property string         $fingerprint
 * @property string|null    $serial_number
 * @property boolean        $is_blocked
 * @property boolean        $is_active
 * @property Carbon         $last_seen_at
 * @property string|null    $blocked_reason
 * @property Carbon|null    $blocked_at
 * @property array|null     $device_info
 * @property array|null     $capabilities
 * @property array|null     $settings
 * @property float|null     $latitude
 * @property float|null     $longitude
 * @property string|null    $location_name
 * @property array|null     $allowed_ip_ranges
 * @property Carbon         $last_heartbeat_at
 * @property string|null    $last_known_ip
 * @property int|null       $failed_auth_attempts
 * @property Carbon|null    $last_failed_auth_at
 * @property int|null       $firmware_version
 * @property string         $app_version
 * @property boolean        $needs_update
 * @property int            $total_transactions
 * @property float          $uptime_percentage
 * @property float          $avg_response_time_ms
 * @property Store          $store
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 *
 */
class Device extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens, LogsActivity, Timestamp;

    protected $table = 'devices';

    protected $fillable = [
        'store_id',
        'name',
        'type',
        'fingerprint_hash',
        'fingerprint',
        'serial_number',
        'is_blocked',
        'last_seen_at',
        'is_active',
        'blocked_reason',
        'blocked_at',
        'device_info',
        'capabilities',
        'settings',
        'latitude',
        'longitude',
        'location_name',
        'allowed_ip_ranges',
        'last_heartbeat_at',
        'last_known_ip',
        'failed_auth_attempts',
        'last_failed_auth_at',
        'firmware_version',
        'app_version',
        'needs_update',
        'last_updated_at',
        'total_transactions',
        'uptime_percentage',
        'avg_response_time_ms',
    ];

    protected $casts = [
        'type'                 => DeviceType::class,
        'is_active'            => 'boolean',
        'is_blocked'           => 'boolean',
        'device_info'          => 'array',
        'capabilities'         => 'array',
        'last_failed_auth_at'  => 'datetime',
        'last_updated_at'      => 'datetime',
        'last_heartbeat_at'    => 'datetime',
        'blocked_at'           => 'datetime',
        'settings'             => 'array',
        'latitude'             => 'decimal:8',
        'longitude'            => 'decimal:8',
        'allowed_ip_ranges'    => 'array',
        'needs_update'         => 'boolean',
        'uptime_percentage'    => 'decimal:8',
        'avg_response_time_ms' => 'decimal:8',
        'last_seen_at'         => 'datetime'
    ];

    protected $hidden = [
        'fingerprint',
        'fingerprint_hash'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function deviceLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('is_active', true)->where('is_blocked', false);
    }

    public function scopeByType(Builder $query, DeviceType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    public function scopeOnline(Builder $query, int $minutesThreshold = 5): Builder
    {
        return $query->where('last_heartbeat_at', '>', now()->subMinutes($minutesThreshold));
    }

    public function scopeOffline(Builder $query, int $minutesThreshold = 5): Builder
    {
        return $query->where('last_heartbeat_at', '<', now()->subMinutes($minutesThreshold))
            ->orWhereNull('last_heartbeat_at');
    }

    public function scopeNeedsAttention(Builder $query): Builder
    {
        return $query->when(function (Builder $q){
            $q->where('is_blocked', true)
                ->orWhere('needs_update', true)
                ->orWhere('failed_auth_attempts', '>', 5)
                ->orWhere('last_seen_at', '<', now()->subHours(24));
        });
    }

    public function isOnline(int $minutesThreshold = 5): bool
    {
        return $this->last_heartbeat_at &&
            $this->last_heartbeat_at->greaterThan(now()->subMinutes($minutesThreshold));
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_blocked) return 'blocked';
        if (! $this->is_active) return 'inactive';
        if ($this->isOnline()) return 'online';
        if ($this->last_heartbeat_at) return 'offline';
        return 'never_connected';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'online' => 'green',
            'offline' => 'yellow',
            'blocked' => 'red',
            'inactive' => 'gray',
            'never_connected' => 'gray',
            default => 'gray',
        };
    }

    public function heartbeat(string $ipAddress, array $systemInfo = []): void
    {
        $this->update([
            'last_heartbeat_at' => now(),
            'last_seen_at' => now(),
            'last_known_ip' => $ipAddress,
            'device_info' => array_merge($this->device_info ?? [], $systemInfo),
            'failed_auth_attempts' => 0,
        ]);
    }

    public function recordFailedAuth(): void
    {
        $this->increment('failed_auth_attempts');
        $this->update(['last_failed_auth_at' => now()]);

        if ($this->failed_auth_attempts >= 10) {
            $this->block('Trop de tentatives de connexion échouées');
        }
    }

    public function block(string $reason = null): void
    {
        $this->update([
            'is_blocked' => true,
            'blocked_reason' => $reason,
            'blocked_at' => now(),
        ]);

        $this->tokens()->delete();
    }

    public function unblock(): void
    {
        $this->update([
            'is_blocked'           => false,
            'blocked_reason'       => null,
            'blocked_at'           => null,
            'failed_auth_attempts' => 0,
        ]);
    }

    public function recordTransaction(): void
    {
        $this->increment('total_transactions');
    }

    public function updatePerformanceMetrics(int $responseTimeMs): void
    {
        $currentAvg = $this->avg_response_time_ms;
        $newAvg = $currentAvg === 0 ? $responseTimeMs : ($currentAvg + $responseTimeMs) / 2;

        $this->update(['avg_response_time_ms' => $newAvg]);
    }

    public function calculateUptime(): float
    {
        if (! $this->created_at) return 0.0;

        $totalTime = now()->diffInMinutes($this->created_at);
        $onlineTime = $totalTime;

        return $totalTime > 0 ? ($onlineTime / $totalTime) * 100 : 0.0;
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    public function isIpAllowed(string $ipAddress): bool
    {
        if (! $this->allowed_ip_ranges) {
            return true;
        }

        foreach ($this->allowed_ip_ranges as $range) {
            if ($this->ipInRange($ipAddress, $range)) {
                return true;
            }
        }

        return false;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'is_active', 'is_blocked', 'settings'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    private function ipInRange(string $ip, string $range): bool
    {
        if (str_contains($range, '/')) {
            [$subnet, $mask] = explode('/', $range);
            return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
        }

        return $ip === $range;
    }
}
