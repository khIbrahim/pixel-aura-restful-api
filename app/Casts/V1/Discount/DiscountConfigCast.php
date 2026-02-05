<?php

namespace App\Casts\V1\Discount;

use App\Contracts\V1\Discount\Config\DiscountConfigInterface;
use App\Enum\V1\Discount\DiscountType;
use App\ValueObjects\V1\DiscountConfig\{BuyXGetYConfig, DiscountQuantityConfig, HappyHourConfig};
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class DiscountConfigCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?DiscountConfigInterface
    {
        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $typeValue = $attributes['type'] ?? null;

        if (! $typeValue) {
            return null;
        }

        $type = $typeValue instanceof DiscountType ? $typeValue : DiscountType::from($typeValue);

        return $this->createDiscountConfig($type, $data);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DiscountConfigInterface) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        throw new InvalidArgumentException(
            "La config doit être une instance de " . DiscountConfigInterface::class . " ou un tableau."
        );
    }

    private function createDiscountConfig(DiscountType $type, array $data): ?DiscountConfigInterface
    {
        return match($type) {
            DiscountType::BuyXGetYFree => BuyXGetYConfig::fromArray($data),
            DiscountType::HappyHour    => HappyHourConfig::fromArray($data),
            DiscountType::Quantity     => DiscountQuantityConfig::fromArray($data),

            default => null,
        };
    }

    public static function getTypeClass(DiscountType $type): ?string
    {
        return match($type) {
            DiscountType::BuyXGetYFree => BuyXGetYConfig::class,
            DiscountType::HappyHour    => HappyHourConfig::class,
            DiscountType::Quantity     => DiscountQuantityConfig::class,
            default                    => null,
        };
    }
}
