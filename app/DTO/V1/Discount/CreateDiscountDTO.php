<?php

namespace App\DTO\V1\Discount;

use App\Casts\V1\Discount\DiscountConditions;
use App\Casts\V1\Discount\DiscountTargets;
use App\Contracts\V1\Discount\Config\DiscountConfigInterface;
use App\DTO\V1\Abstract\BaseDTO;
use App\Enum\V1\Discount\DiscountStatus;
use App\Enum\V1\Discount\DiscountType;
use Carbon\Carbon;

final readonly class CreateDiscountDTO extends BaseDTO
{

    public function __construct(
        public string                   $name,
        public string                   $code,
        public ?string                  $description = null,
        public DiscountType             $type,
        public DiscountStatus           $status = DiscountStatus::Draft,
        public int                      $value,
        public ?DiscountConfigInterface $config      = null,
        public ?DiscountTargets         $targets = null,
        public ?Carbon                  $valid_from = null,
        public ?Carbon                  $valid_until = null,
        public ?int                     $max_uses = null,
        public ?int                     $max_uses_per_customer = null,
        public ?int                     $max_discount_cents = null,
        public bool                     $is_combinable = false,
        public array                    $combinable_with = [],
        public array                    $metadata = [],
        public ?DiscountConditions      $conditions = null,
        public int                      $store_id,
        public ?int                     $created_by = null
    ){}

    public function toArray(): array
    {
        return [
            'name'                   => $this->name,
            'code'                   => $this->code,
            'description'            => $this->description,
            'type'                   => $this->type->value,
            'value'                  => $this->value,
            'config'                 => $this->config->toArray(),
            'status'                 => $this->status->value,
            'targets'                => $this->targets?->toArray(),
            'conditions'             => $this->conditions?->toArray(),
            'valid_from'             => $this->valid_from?->toDateTimeString(),
            'valid_until'            => $this->valid_until?->toDateTimeString(),
            'max_uses'               => $this->max_uses,
            'max_uses_per_customer'  => $this->max_uses_per_customer,
            'max_discount_cents'     => $this->max_discount_cents,
            'is_combinable'          => $this->is_combinable,
            'combinable_with'        => $this->combinable_with,
            'metadata'               => $this->metadata,
            'store_id'               => $this->store_id,
        ];
    }
}
