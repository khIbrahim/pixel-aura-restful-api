<?php

namespace App\Models\V1;

use App\Casts\V1\Discount\DiscountConditions;
use App\Casts\V1\Discount\DiscountConfigCast;
use App\Casts\V1\Discount\DiscountTargets;
use App\Contracts\V1\Discount\Config\DiscountConfigInterface;
use App\Enum\V1\Discount\DiscountStatus;
use App\Enum\V1\Discount\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int                     $id
 * @property int                     $store_id
 * @property string                  $name
 * @property string|null             $code
 * @property string|null             $description
 * @property DiscountType            $type
 * @property DiscountStatus          $status
 * @property int                     $value
 * @property DiscountConfigInterface $config
 * @property DiscountTargets         $target
 * @property DiscountConditions      $conditions
 * @property Carbon|null             $valid_from
 * @property Carbon|null             $valid_until
 * @property int|null                $max_uses
 * @property int|null                $max_uses_per_customer
 * @property int                     $current_uses
 * @property bool                    $is_combinable
 * @property array|null              $combinable_with
 * @property bool                    $is_active
 * @property array|null              $metadata
 * @property int|null                $created_by
 * @property Carbon|null             $applied_at
 * @property int|null                $applied_by
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 * @property Store                   $store
 * @property StoreMember|null        $creator
 * @property StoreMember|null        $applier
 */
class Discount extends Model
{
    protected $table = 'discounts';

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'description',
        'type',
        'status',
        'value',
        'config',
        'conditions',
        'target',
        'valid_from',
        'valid_until',
        'max_uses',
        'max_uses_per_customer',
        'current_uses',
        'is_combinable',
        'combinable_with',
        'is_active',
        'metadata',
        'created_by',
        'applied_at',
        'applied_by',
    ];

    protected $casts = [
        'type'              => DiscountType::class,
        'status'            => DiscountStatus::class,
        'config'            => DiscountConfigCast::class,
        'valid_from'        => 'datetime',
        'valid_until'       => 'datetime',
        'is_combinable'     => 'boolean',
        'combinable_with'   => 'array',
        'is_active'         => 'boolean',
        'metadata'          => 'array',
        'applied_at'        => 'datetime',
        'target'            => DiscountTargets::class,
        'conditions'        => DiscountConditions::class,
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(StoreMember::class, 'created_by');
    }

    public function applier(): BelongsTo
    {
        return $this->belongsTo(StoreMember::class, 'applied_by');
    }

    public function getEffectiveStatus(): DiscountStatus
    {
        if(in_array($this->status, [DiscountStatus::Expired, DiscountStatus::Depleted, DiscountStatus::Paused, DiscountStatus::Draft, DiscountStatus::Cancelled])){
            return $this->status;
        }

        $now = Carbon::now();
        if($this->valid_from && $now->lt($this->valid_from)){
            return DiscountStatus::Scheduled;
        }

        if($this->valid_until && $now->gt($this->valid_until)){
            return DiscountStatus::Expired;
        }

        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return DiscountStatus::Depleted;
        }

        return DiscountStatus::Active;
    }

    public function isApplicable(): bool
    {
        return $this->getEffectiveStatus()->isApplicable();
    }

}
