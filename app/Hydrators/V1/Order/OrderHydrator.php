<?php

namespace App\Hydrators\V1\Order;

use App\DTO\V1\Order\OrderData;
use App\DTO\V1\Order\OrderItemData;
use App\DTO\V1\Order\OrderIngredientData;
use App\DTO\V1\Order\OrderOptionData;
use App\DTO\V1\Order\ServiceType\DeliveryInfo;
use App\DTO\V1\Order\ServiceType\DineInInfo;
use App\DTO\V1\Order\ServiceType\PickupInfo;
use App\Enum\V1\Order\OrderChannel;
use App\Enum\V1\Order\OrderIngredientAction;
use App\Enum\V1\Order\OrderServiceType;
use App\Http\Requests\V1\Order\CreateOrderRequest;
use App\Hydrators\V1\BaseHydrator;
use App\Models\V1\Store;

final class OrderHydrator extends BaseHydrator
{
    public function fromCreateRequest(CreateOrderRequest $request): OrderData
    {
        $data = $request->validated();

        /** @var Store $store */
        $store = $request->attributes->get('store');

        return new OrderData(
            store_id: $store->id,
            channel: OrderChannel::from($data['channel']),
            service_type: OrderServiceType::from($data['service_type']),
            device_id: $request->attributes->get('device')->id,
            created_by: $request->attributes->get('store_member')?->id,
            currency: $data['currency'] ?? $store->currency,
            items: array_map(fn(array $item) => $this->hydrateItem($item), $data['items']),
            special_instructions: $data['special_instructions'] ?? null,
            delivery: array_key_exists('delivery', $data) ? $this->hydrateDelivery($data['delivery']) : null,
            dine_in: array_key_exists('dine_in', $data) ? $this->hydrateDineIn($data['dine_in'] ?? null) : null,
            pickup: array_key_exists('pickup', $data) ? $this->hydratePickup($data['pickup'] ?? null) : null
        );
    }

    private function hydrateItem(array $itemData): OrderItemData
    {
        return new OrderItemData(
            item_id: (int) $itemData['id'],
            variant_id: array_key_exists('variant_id', $itemData) ? (int) $itemData['variant_id'] : null,
            quantity: (int) $itemData['quantity'],
            options: array_map(fn(array $optionData) => $this->hydrateOption($optionData), $itemData['selected_options'] ?? []),
            modifications: array_map(fn(array $modData) => $this->hydrateIngredient($modData), $itemData['ingredient_modifications'] ?? []),
            special_instructions: array_key_exists('special_instructions', $itemData) ? (string) $itemData['special_instructions'] : null,
        );
    }

    private function hydrateOption(array $optionData): OrderOptionData
    {
        $optionId = (int) $optionData['id'];
        $quantity = (int) ($optionData['quantity'] ?? 1);
        return new OrderOptionData(
            option_id: $optionId,
            quantity: $quantity
        );
    }

    private function hydrateIngredient(array $modData): OrderIngredientData
    {
        $ingredientId = (int) $modData['id'];
        $action       = OrderIngredientAction::from((string) $modData['action']);
        $quantity     = $action === OrderIngredientAction::Add ? (int) ($modData['quantity'] ?? 1) : null;
        return new OrderIngredientData(
            ingredient_id: $ingredientId,
            action: $action,
            quantity: $quantity
        );
    }

    private function hydrateDelivery(array $deliveryData): DeliveryInfo
    {
        return new DeliveryInfo(
            address: (string) $deliveryData['address'],
            contact_name: (string) $deliveryData['contact_name'],
            contact_phone: (string) $deliveryData['contact_phone'],
            notes: array_key_exists('notes', $deliveryData) ? (string) $deliveryData['notes'] : null,
            fee_cents: array_key_exists('fee_cents', $deliveryData) ? (int) $deliveryData['fee_cents'] : 0,
        );
    }

    private function hydrateDineIn(array $dineInData): ?DineInInfo
    {
        return new DineInInfo(
            table_number: (string) $dineInData['table_number'],
            number_of_guests: (int) ($dineInData['number_of_guests'] ?? 1),
            server_id: array_key_exists('server_id', $dineInData) ? (int) $dineInData['server_id'] : null,
        );
    }

    private function hydratePickup(array $pickupData): ?PickupInfo
    {
        return new PickupInfo(
            contact_name: (string) $pickupData['contact_name'],
        );
    }

}
