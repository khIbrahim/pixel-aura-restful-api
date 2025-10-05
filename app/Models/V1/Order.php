<?php

namespace App\Models\V1;

use App\DTO\V1\Order\ServiceType\DeliveryInfo;
use App\DTO\V1\Order\ServiceType\DineInInfo;
use App\DTO\V1\Order\ServiceType\PickupInfo;
use App\Enum\V1\Order\OrderChannel;
use App\Enum\V1\Order\OrderServiceType;
use App\Enum\V1\Order\OrderStatus;
use App\Traits\V1\Model\HasMoneyAttributes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int                   $id
 * @property string                $number
 * @property int                   $store_id
 * @property OrderChannel          $channel
 * @property OrderServiceType      $service_type
 * @property int                   $device_id
 * @property Device                $device
 * @property int                   $created_by
 * @property StoreMember|null      $creator
 * @property int                   $subtotal_cents
 * @property int                   $tax_cents
 * @property int                   $discount_cents
 * @property int                   $total_cents
 * @property string                $currency
 * @property OrderStatus           $status
 * @property Carbon                $confirmed_at
 * @property Carbon                $preparing_at
 * @property Carbon                $ready_at
 * @property Carbon                $completed_at
 * @property Carbon                $cancelled_at
 * @property Carbon                $created_at
 * @property Carbon                $updated_at
 * @property Carbon                $refunded_at
 * @property array                 $metadata
 * @property string                $special_instructions
 * @property Collection<OrderItem> $items
 * @property Store                 $store
 * @property DeliveryInfo|null     $delivery
 * @property DineInInfo|null       $dine_in
 * @property PickupInfo|null       $pickup
 * @property int|null              $estimated_preparation_minutes
 * @property Carbon|null           $excepted_ready_at
 */
class Order extends Model
{
    use HasMoneyAttributes;

    protected $table = 'orders';

    protected $fillable = [
        'number',
        'store_id',
        'channel',
        'service_type',
        'device_id',
        'created_by',
        'subtotal_cents',
        'tax_cents',
        'discount_cents',
        'total_cents',
        'currency',
        'status',
        'confirmed_at',
        'preparing_at',
        'ready_at',
        'completed_at',
        'cancelled_at',
        'metadata',
        'special_instructions',
        'delivery',
        'dine_in',
        'pickup',
        'estimated_preparation_minutes',
        'excepted_ready_at'
    ];

    protected $casts = [
        'confirmed_at'      => 'datetime',
        'preparing_at'      => 'datetime',
        'ready_at'          => 'datetime',
        'completed_at'      => 'datetime',
        'cancelled_at'      => 'datetime',
        'metadata'          => 'array',
        'channel'           => OrderChannel::class,
        'service_type'      => OrderServiceType::class,
        'status'            => OrderStatus::class,
        'delivery'          => DeliveryInfo::class,
        'dine_in'           => DineInInfo::class,
        'pickup'            => PickupInfo::class,
        'excepted_ready_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(StoreMember::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function getPreparationData(): array
    {
        $elapsedSeconds = $this->confirmed_at ? now()->diffInSeconds($this->confirmed_at) : 0;
        $elapsedMinutes = $elapsedSeconds / 60;

        $remainingSeconds = $this->excepted_ready_at ? $this->excepted_ready_at->diffInSeconds(now()) : 0;
        $remainingMinutes = $remainingSeconds / 60;

        if ($this->excepted_ready_at && $this->excepted_ready_at->isPast()) {
            $remainingMinutes = 0;
        }

        return [
            'estimated_minutes'   => $this->estimated_preparation_minutes,
            'excepted_ready_at'   => $this->when($this->excepted_ready_at !== null, fn() => $this->excepted_ready_at?->toIso8601String()),
            'elapsed_minutes'     => round(-1 * $elapsedMinutes, 2),
            'remaining_time'      => $this->excepted_ready_at ? round(-1 * $remainingMinutes, 2) : null,
        ];
    }

}
