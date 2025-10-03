<?php

namespace App\Services\V1\Order;

use App\DTO\V1\Order\ItemPricing;
use App\DTO\V1\Order\OrderData;
use App\DTO\V1\Order\OrderItemData;
use App\DTO\V1\Order\OrderIngredientData;
use App\DTO\V1\Order\OrderOptionData;
use App\DTO\V1\Order\OrderPricing;

class OrderPriceCalculatorService
{

    public function calculate(OrderData $data): OrderData
    {
        $pricedItems   = array_map(fn(OrderItemData $itemData) => $this->calculateOrderItemPricing($itemData), $data->items);
        $subtotalCents = collect($pricedItems)->sum(fn(OrderItemData $itemData) => $itemData->pricing->final_total_cents ?? 0);

        $taxRate = /** $this->taxService->getTaxRateForStore($data->store_id); */ 0.00;
        $taxCents = (int) round($subtotalCents * $taxRate);
        $totalCents = $subtotalCents + $taxCents;

        $orderPricing = new OrderPricing(
            subtotal_cents: $subtotalCents,
            tax_cents: $taxCents,
//            tax_rate: $taxRate,
            total_cents: $totalCents,
        );

        if ($data->service_type->isDelivery()) {
            $orderPricing->delivery_fee_cents = $data->delivery->fee_cents;
        }

        return new OrderData(
            store_id: $data->store_id,
            channel: $data->channel,
            service_type: $data->service_type,
            device_id: $data->device_id,
            created_by: $data->created_by,
            currency: $data->currency,
            items: $pricedItems,
            special_instructions: $data->special_instructions,
            pricing: $orderPricing,
            number: $data->number,
            metadata: $data->metadata,
            delivery: $data->delivery,
            dine_in: $data->dine_in,
            pickup: $data->pickup,
        );
    }

    private function calculateOrderItemPricing(OrderItemData $itemData): OrderItemData
    {
        $basePriceCents = $itemData->getBasePriceCents();

        $optionsPriceCents = collect($itemData->options)
            ->sum(fn(OrderOptionData $optionData) => $optionData->getTotalPriceCents());

        $modificationPriceCents = collect($itemData->modifications)
            ->sum(fn(OrderIngredientData $modData) => $modData->getTotalPriceCents());

        $itemTotalCents      = ($basePriceCents + $optionsPriceCents + $modificationPriceCents);
        $finalTotalCents     = $itemTotalCents * $itemData->quantity;

        $pricing = new ItemPricing(
            base_price_cents: $basePriceCents,
            options_price_cents: $optionsPriceCents,
            modifications_price_cents: $modificationPriceCents,
            item_total_cents: $itemTotalCents,
            final_total_cents: $finalTotalCents
        );

        return $itemData->setPricing($pricing);
    }

}
