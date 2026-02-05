<?php

namespace App\Http\Resources\V1;

use App\Enum\V1\Discount\DiscountStatus;
use App\Enum\V1\Discount\DiscountType;
use App\Models\V1\Discount;
use App\ValueObjects\V1\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Discount
 */
class DiscountResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $effectiveStatus = $this->getEffectiveStatus();

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'code'        => $this->code,
            'description' => $this->description,
            'type'        => [
                'value'       => $this->type->value,
                'label'       => $this->type->label(),
                'description' => $this->type->description(),
            ],
            'discount'    => $this->formatDiscount(),

            'status' => [
                'current'              => $effectiveStatus->value,
                'label'                => $effectiveStatus->label(),
                'is_applicable'        => $effectiveStatus->isApplicable(),
            ],

            'config'        => $this->when($this->config !== null, fn() => $this->config?->toArray()),
            'targeting'     => $this->formatTargeting(),
            'conditions'    => $this->when($this->conditions !== null, fn() => $this->formatConditions()),
            'validity'      => $this->formatValidity($effectiveStatus),
            'usage'         => $this->formatUsage(),
            'applicability' => [
                'is_combinable'   => $this->is_combinable,
                'combinable_with' => $this->combinable_with,
            ],

            'store' => $this->whenLoaded('store', fn() => [
                'id'   => $this->store->id,
                'name' => $this->store->name,
            ]),

            'creator' => $this->whenLoaded('creator', fn() => [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
                'role' => $this->creator->role->value,
            ]),

            'metadata' => $this->metadata,

            'timestamps' => [
                'created_at'  => $this->created_at->toIso8601String(),
                'updated_at'  => $this->updated_at->toIso8601String(),
                'valid_from'  => $this->valid_from?->toIso8601String(),
                'valid_until' => $this->valid_until?->toIso8601String(),
            ],
        ];
    }

    private function formatDiscount(): array
    {
        return [
            'raw'       => $this->value,
            'display'   => $this->getDisplayValue(),
            'formatted' => $this->getFormattedValue(),
        ];
    }

    private function formatTargeting(): array
    {
        $targeting = [];

        if (! empty($this->target->applicable_items)) {
            $targeting['items'] = $this->target->applicable_items;
        }

        if (! empty($this->target->applicable_categories)) {
            $targeting['categories'] = $this->target->applicable_categories;
        }

        if (! empty($this->target->excluded_items)) {
            $targeting['excluded_items'] = $this->target->excluded_items;
        }

        $targeting['scope'] = $this->getTargetingScope();

        return $targeting;
    }


    private function getTargetingScope(): string
    {
        if (! empty($this->target->applicable_items)) {
            return 'items';
        }

        if (! empty($this->target->applicable_categories)) {
            return 'categories';
        }

        if ($this->type->value === 'free_delivery') {
            return 'delivery';
        }

        return 'order';
    }

    private function formatConditions(): array
    {
        $conditions = [];

        if ($this->conditions->min_order_amount_cents) {
            $conditions['min_order_amount'] = [
                'cents'     => $this->conditions->min_order_amount_cents,
                'formatted' => $this->formatMoney($this->conditions->min_order_amount_cents),
            ];
        }

        if ($this->conditions->max_order_amount_cents) {
            $conditions['max_order_amount'] = [
                'cents'     => $this->conditions->max_order_amount_cents,
                'formatted' => $this->formatMoney($this->conditions->max_order_amount_cents),
            ];
        }

        if ($this->conditions->min_items_quantity) {
            $conditions['min_items'] = $this->conditions->min_items_quantity;
        }

        if ($this->conditions->min_customer_orders) {
            $conditions['min_customer_orders'] = $this->conditions->min_customer_orders;
        }

        return $conditions;
    }

    private function formatValidity(DiscountStatus $effectiveStatus): array
    {
        $validity = [
            'is_active'      => $this->is_active,
            'has_time_range' => $this->valid_from !== null || $this->valid_until !== null,
        ];

        if ($this->valid_from) {
            $validity['starts_at'] = $this->valid_from->toIso8601String();
            $validity['starts_in'] = $this->valid_from->isFuture() ? $this->valid_from->diffForHumans() : null;
        }

        if ($this->valid_until) {
            $validity['ends_at'] = $this->valid_until->toIso8601String();
            $validity['ends_in'] = $this->valid_until->isFuture() ? $this->valid_until->diffForHumans() : null;
        }

        if (! $effectiveStatus->isApplicable()) {
            $validity['ineligibility_reason'] = $this->getIneligibilityReason($effectiveStatus);
        }

        return $validity;
    }

    private function formatUsage(): ?array
    {
        if (! $this->max_uses && !$this->max_uses_per_customer) {
            return [
                'has_limits' => false,
            ];
        }

        $usage = [
            'has_limits' => true,
            'current'    => $this->current_uses,
        ];

        if ($this->max_uses) {
            $usage['max_total']        = $this->max_uses;
            $usage['remaining']        = max(0, $this->max_uses - $this->current_uses);
            $usage['progress_percent'] = min(100, ($this->current_uses / $this->max_uses) * 100);
        }

        if ($this->max_uses_per_customer) {
            $usage['max_per_customer'] = $this->max_uses_per_customer;
        }

        return $usage;
    }

    private function getIneligibilityReason(DiscountStatus $status): string
    {
        return match($status) {
            DiscountStatus::Draft     => "Ce discount est en brouillon",
            DiscountStatus::Scheduled => "Commence le " . $this->valid_from?->format('d/m/Y à H:i'),
            DiscountStatus::Expired   => "Expiré le " . $this->valid_until?->format('d/m/Y à H:i'),
            DiscountStatus::Depleted  => "Quota épuisé ($this->current_uses/$this->max_uses)",
            DiscountStatus::Paused    => "Mis en pause",
            DiscountStatus::Cancelled => "Annulé",
            default => "",
        };
    }

    private function formatMoney(int $cents): string
    {
        return Money::of($cents, $this->store->currency)->formatted();
    }

    private function getDisplayValue(): ?float
    {
        if ($this->value === null) {
            return null;
        }

        return match($this->type) {
            DiscountType::Percentage, DiscountType::FirstOrder, DiscountType::HappyHour, DiscountType::FixedAmount, DiscountType::ReduceDelivery => $this->value / 100,
            default => null,
        };
    }

    private function getFormattedValue(): string
    {
        if ($this->value === null) {
            return match($this->type) {
                DiscountType::FreeDelivery => 'Gratuit',
                DiscountType::BuyXGetYFree => 'Variable',
                default                    => 'N/A',
            };
        }

        return match($this->type) {
            DiscountType::Percentage, DiscountType::FirstOrder, DiscountType::HappyHour => ($this->value / 100) . '%',
            DiscountType::FixedAmount, DiscountType::ReduceDelivery => $this->formatMoney($this->value),
            DiscountType::Quantity  => $this->value . ' items',

            default => (string) $this->value,
        };
    }

}
