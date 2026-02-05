<?php

namespace App\Hydrators\V1\Discount;

use App\Casts\V1\Discount\DiscountConditions;
use App\Casts\V1\Discount\DiscountConfigCast;
use App\Casts\V1\Discount\DiscountTargets;
use App\Contracts\V1\Discount\Config\DiscountConfigInterface;
use App\DTO\V1\Discount\CreateDiscountDTO;
use App\Enum\V1\Discount\DiscountStatus;
use App\Enum\V1\Discount\DiscountType;
use App\Http\Requests\V1\Discount\CreateDiscountRequest;
use App\Hydrators\V1\BaseHydrator;
use Carbon\Carbon;

final class DiscountHydrator extends BaseHydrator
{

    public function fromCreateRequest(CreateDiscountRequest $request): CreateDiscountDTO
    {
        $data = $request->validated();

        if (isset($data['type'])){
            $data['type'] = DiscountType::from(strtolower($data['type']));
        }

        $data['status'] = DiscountStatus::Draft;

        if (isset($data['valid_from'])){
            $data['valid_from'] = Carbon::make($data['valid_from']);
        }

        if ($data['valid_until']) {
            $data['valid_until'] = Carbon::make($data['valid_until']);
        }

        if (isset($data['applicable_items']) || isset($data['applicable_categories']) || isset($data['excluded_items']) || isset($data['excluded_categories'])) {
            $data['targets'] = new DiscountTargets(
                applicable_items: $data['applicable_items'] ?? [],
                applicable_categories: $data['applicable_categories'] ?? [],
                excluded_items: $data['excluded_items'] ?? [],
                excluded_categories: $data['excluded_categories'] ?? [],
            );
        }

        if (isset($data['min_order_amount_cents']) || isset($data['min_items_quantity']) || isset($data['min_customer_orders']) || isset($data['max_order_amount_cents'])) {
            $data['conditions'] = new DiscountConditions(
                min_order_amount_cents: $data['min_order_amount_cents'] ?? null,
                min_items_quantity: $data['min_items_quantity'] ?? null,
                min_customer_orders: $data['min_customer_orders'] ?? null,
                max_order_amount_cents: $data['max_order_amount_cents'] ?? null,
            );
        }

        if (isset($data['config']) && is_array($data['config'])) {
            /** @var null|DiscountConfigInterface $config */
            $config = DiscountConfigCast::getTypeClass($data['type']);
            if ($config !== null) {
                $data['config'] = $config::fromArray($data['config']);
            }
        }

        $data['store_id'] = $request->attributes->get('store')->id;
        if($request->attributes->get('store_member') !== null){
            $data['created_by'] = $request->attributes->get('store_member')->id;
        }

        return $this->fromArray($data, CreateDiscountDTO::class);
    }

}
