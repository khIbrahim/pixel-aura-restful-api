<?php

namespace App\Services\V1\Order;

use App\DTO\V1\Order\OrderData;
use App\DTO\V1\Order\OrderItemData;
use App\DTO\V1\Order\OrderIngredientData;
use App\DTO\V1\Order\OrderOptionData;
use App\DTO\V1\Order\ServiceType\DeliveryInfo;
use App\DTO\V1\Order\ServiceType\DineInInfo;
use App\DTO\V1\Order\ServiceType\PickupInfo;
use App\Enum\V1\Order\OrderIngredientAction;
use App\Enum\V1\Order\OrderServiceType;
use App\Exceptions\V1\Order\OrderCreationException;

class OrderValidationService
{

    private const int MIN_ITEM_QUANTITY           = 1;
    private const int MAX_ITEM_QUANTITY           = 100;
    private const int MIN_OPTION_QUANTITY         = 1;
    private const int MAX_OPTION_QUANTITY         = 10;
    private const int MIN_INGREDIENT_ADD_QUANTITY = 1;
    private const int MAX_INGREDIENT_ADD_QUANTITY = 5;

    /**
     * @throws OrderCreationException
     */
    public function validate(OrderData $data): void
    {
        $this->validateOrder($data);

        foreach ($data->items as $item) {
            $this->validateItem($item);
        }
    }

    /**
     * @throws OrderCreationException
     */
    private function validateOrder(OrderData $data): void
    {
        if (empty($data->items)) {
            throw OrderCreationException::noItemsProvided();
        }

        $this->validateServiceTypeDetails($data);
    }

    /**
     * @throws OrderCreationException
     */
    private function validateServiceTypeDetails(OrderData $data): void
    {
        $serviceType = $data->service_type;
        match ($serviceType) {
            OrderServiceType::DineIn   => $this->validateDineInDetails($data),
            OrderServiceType::Delivery => $this->validateDeliveryDetails($data),
            OrderServiceType::Pickup   => $this->validatePickupDetails($data),
        };
    }

    /**
     * @throws OrderCreationException
     */
    private function validateDineInDetails(OrderData $data): void
    {
        if ($data->dine_in === null) {
            throw OrderCreationException::missingDineInDetails();
        }

        if ($data->dine_in instanceof DineInInfo){
            if (empty($data->dine_in->table_number)) {
                throw OrderCreationException::invalidDineInDetails('Le numéro de table est requis pour les commandes sur place.');
            }
        } else {
            throw OrderCreationException::invalidDineInDetails('Les informations de repas sur place sont invalides.');
        }
    }

    /**
     * @throws OrderCreationException
     */
    private function validateDeliveryDetails(OrderData $data): void
    {
        if ($data->delivery === null) {
            throw OrderCreationException::missingDeliveryDetails();
        }

        if($data->delivery instanceof DeliveryInfo){
            if (empty($data->delivery->address)) {
                throw OrderCreationException::invalidDeliveryDetails('L\'adresse est requise pour les commandes de livraison.');
            }
            if (empty($data->delivery->contact_name)) {
                throw OrderCreationException::invalidDeliveryDetails('Le nom du contact est requis pour les commandes de livraison.');
            }
            if (empty($data->delivery->contact_phone)) {
                throw OrderCreationException::invalidDeliveryDetails('Le téléphone du contact est requis pour les commandes de livraison.');
            }
        } else {
            throw OrderCreationException::invalidDeliveryDetails('Les informations de livraison sont invalides.');
        }
    }

    /**
     * @throws OrderCreationException
     */
    private function validatePickupDetails(OrderData $data): void
    {
        if($data->pickup === null){
            throw OrderCreationException::missingPickupDetails();
        }

        if($data->pickup instanceof PickupInfo){
            if(empty($data->pickup->contact_name)){
                throw OrderCreationException::invalidPickupDetails('Le nom du contact est requis pour les commandes de cueillette.');
            }
        } else {
            throw OrderCreationException::invalidPickupDetails('Les informations de cueillette sont invalides.');
        }
    }

    /**
     * @throws OrderCreationException
     */
    private function validateItem(OrderItemData $item): void
    {
        $this->validateItemQuantity($item);
        $this->validateOptions($item);
        $this->validateModifications($item);
    }

    /**
     * @throws OrderCreationException
     */
    private function validateItemQuantity(OrderItemData $item): void
    {
        if ($item->quantity < self::MIN_ITEM_QUANTITY || $item->quantity > self::MAX_ITEM_QUANTITY) {
            throw OrderCreationException::invalidQuantity($item->item_id, $item->quantity, self::MIN_ITEM_QUANTITY, self::MAX_ITEM_QUANTITY);
        }
    }

    /**
     * @throws OrderCreationException
     */
    private function validateOptions(OrderItemData $item): void
    {
        foreach ($item->options as $option) {
            $this->validateOption($option);
        }
    }

    /**
     * @throws OrderCreationException
     */
    private function validateOption(OrderOptionData $option): void
    {
        if ($option->quantity < self::MIN_OPTION_QUANTITY || $option->quantity > self::MAX_OPTION_QUANTITY) {
            throw OrderCreationException::invalidQuantity(
                $option->option_id,
                $option->quantity,
                self::MIN_OPTION_QUANTITY,
                self::MAX_OPTION_QUANTITY
            );
        }
    }

    /**
     * @throws OrderCreationException
     */
    private function validateModifications(OrderItemData $item): void
    {
        foreach ($item->modifications as $modification) {
            $this->validateModification($modification);
        }
    }

    /**
     * @throws OrderCreationException
     */
    private function validateModification(OrderIngredientData $modification): void
    {
        if ($modification->action === OrderIngredientAction::Add) {
            if ($modification->quantity < self::MIN_INGREDIENT_ADD_QUANTITY || $modification->quantity > self::MAX_INGREDIENT_ADD_QUANTITY) {
                throw OrderCreationException::invalidQuantity(
                    $modification->ingredient_id,
                    $modification->quantity,
                    self::MIN_INGREDIENT_ADD_QUANTITY,
                    self::MAX_INGREDIENT_ADD_QUANTITY
                );
            }
        }
    }

}
