<?php

namespace App\Services\V1\Order;

use App\DTO\V1\Order\OrderData;
use App\DTO\V1\Order\OrderItemData;
use App\DTO\V1\Order\OrderIngredientData;
use App\DTO\V1\Order\OrderOptionData;
use App\Exceptions\V1\Order\OrderCreationException;
use App\Models\V1\Ingredient;
use App\Models\V1\Item;
use App\Models\V1\Option;
use Illuminate\Support\Collection;

class OrderDataEnricherService
{

    public function enrich(OrderData $data): OrderData
    {
        $loaded = $this->bulkLoadData($data);

        $data->items = array_map(
            /** @throws OrderCreationException */
            fn(OrderItemData $itemData) => $this->enrichItem($itemData, $loaded), $data->items
        );

        return $data;
    }

    private function bulkLoadData(OrderData $data): array
    {
        $itemIds       = collect($data->items)->pluck('item_id')->unique();
        $variantIds    = collect($data->items)->pluck('variant_id')->unique()->filter();
        $optionIds     = collect($data->items)
            ->flatMap(function(OrderItemData $itemData){
                return array_map(fn(OrderOptionData $optionData) => $optionData->option_id, $itemData->options);
            })->unique();

        $ingredientIds = collect($data->items)
            ->flatMap(function(OrderItemData $itemData){
                return array_map(fn($modification) => $modification->ingredient_id, $itemData->modifications);
            })->unique();

        return [
            'items'       => Item::query()
                ->whereIn('id', $itemIds)
                ->with(['variants' => fn($q) => $q->whereIn('id', $variantIds)])
                ->get()
                ->keyBy('id'),
            'options'     => $optionIds->isEmpty() ? collect() : Option::query()
                ->whereIn('id', $optionIds)
                ->get()
                ->keyBy('id'),
            'ingredients' => $ingredientIds->isEmpty() ? collect() : Ingredient::query()
                ->whereIn('id', $ingredientIds)
                ->get()
                ->keyBy('id')
        ];
    }

    /**
     * @throws OrderCreationException
     */
    private function enrichItem(OrderItemData $itemData, array $loaded): OrderItemData
    {
        /** @var Item $item */
        $item = $loaded['items']->get($itemData->item_id);
        if(! $item){
            throw OrderCreationException::itemNotFound($itemData->item_id);
        }

        $variant = null;
        if($itemData->variant_id){
            $variant = $item->variants()->firstWhere('id', $itemData->variant_id);
            if(! $variant) {
                throw OrderCreationException::itemVariantNotFound($itemData->variant_id);
            }
        }

        $enrichedOptions       = array_map(fn(OrderOptionData $optionData) => $this->enrichOption($optionData, $loaded['options']), $itemData->options);
        $enrichedModifications = array_map(fn(OrderIngredientData $modData) => $this->enrichIngredient($modData, $loaded['ingredients']), $itemData->modifications);

        return new OrderItemData(
            item_id: $itemData->item_id,
            variant_id: $variant?->id ?? null,
            quantity: $itemData->quantity,
            options: $enrichedOptions,
            modifications: $enrichedModifications,
            special_instructions: $itemData->special_instructions,
            item: $item,
            itemVariant: $variant,
            pricing: $itemData->pricing,
        );
    }

    /**
     * @throws OrderCreationException
     */
    private function enrichOption(OrderOptionData $optionData, Collection $options): OrderOptionData
    {
        $option = $options->get($optionData->option_id);
        if(! $option){
            throw OrderCreationException::optionNotFound($optionData->option_id);
        }

        return $optionData->setOption($option);
    }

    /**
     * @throws OrderCreationException
     */
    private function enrichIngredient(OrderIngredientData $itemData, Collection $ingredients): OrderIngredientData
    {
        $ingredient = $ingredients->get($itemData->ingredient_id);
        if(! $ingredient){
            throw OrderCreationException::ingredientNotFound($itemData->ingredient_id);
        }

        return $itemData->setIngredient($ingredient);
    }

}
