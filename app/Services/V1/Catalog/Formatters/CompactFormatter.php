<?php

namespace App\Services\V1\Catalog\Formatters;

use App\Models\V1\Category;
use App\Models\V1\Item;
use App\Models\V1\ItemVariant;
use App\Models\V1\Option;
use App\Models\V1\Store;
use Illuminate\Support\Collection;

class CompactFormatter implements CatalogFormatterInterface
{

    public function format(Collection $data): array
    {
        /** @var Collection<Category> $categories */
        $categories     = $data['categories'];
        $grouped        = $categories->groupBy('parent_id');
        $rootCategories = $grouped->get(null, collect());

        return [
            'menu_version' => $data['store']->menu_version,
            'store'        => $this->formatStore($data['store']),
            'categories'   => $this->formatCategories($rootCategories, $grouped),
            'generated_at' => now()->toISOString(),
        ];
    }

    private function formatStore(Store $store): array
    {
        return [
            'id'                => $store->id,
            'name'              => $store->name,
            'currency'          => $store->currency,
            'timezone'          => $store->timezone,
            'is_active'         => $store->is_active,
            'email'             => $store->email,
        ];
    }

    private function formatCategories(Collection $categories, Collection $grouped): array
    {
        return $categories->map(function (Category $category) use ($grouped) {
            $subCategories = $grouped->get($category->id, collect());

            return [
                'id'                => $category->id,
                'name'              => $category->name,
                'sku'               => $category->sku,
                'description'       => $category->description,
                'position'          => $category->position,
                'tags'              => $category->tags ?? [],
                'is_active'         => $category->is_active,
                'thumbnail'         => $category->getThumbnailUrl(),
                'items'             => $subCategories->isEmpty() ? $this->formatItems($category->items) : [],
                'subcategories'     => $subCategories->isNotEmpty() ? $this->formatCategories($subCategories, $grouped) : [],
                'has_subcategories' => $subCategories->isNotEmpty(),
            ];
        })->toArray();
    }

    private function formatItems(Collection $items): array
    {
        return $items->map(function (Item $item) {
            return [
                'id'               => $item->id,
                'sku'              => $item->sku,
                'name'             => $item->name,
                'description'      => $item->description,
                'base_price_cents' => $item->base_price_cents,
                'currency'         => $item->currency,
                'thumbnail'        => $item->getThumbnailUrl(),
                'variants'         => $this->formatVariants($item),
                'option_lists'     => $this->formatOptions($item),
            ];
        })->toArray();
    }

    private function formatVariants(Item $item): array
    {
        if ($item->variants->isEmpty()) {
            return [[
                'id'          => null,
                'name'        => 'Standard',
                'price_cents' => $item->base_price_cents,
                'is_default'  => true,
            ]];
        }

        return $item->variants->map(fn (ItemVariant $variant) => [
            'id'          => $variant->id,
            'name'        => $variant->name ?? 'Variant ' . $variant->id,
            'sku'         => $variant->sku,
            'price_cents' => $variant->price_cents ?? $item->base_price_cents,
            'is_active'   => $variant->is_active ?? true,
        ])->toArray();
    }

  private function formatOptions(Item $item): array
  {
      $optionListsFormatted = $item->optionLists->map(function ($optionList) {
          return [
              'list_id'          => $optionList->id,
              'list_name'        => $optionList->name,
              'list_description' => $optionList->description,
              'min_selections'   => $optionList->pivot->min_selections ?? $optionList->min_selections,
              'max_selections'   => $optionList->pivot->max_selections ?? $optionList->max_selections,
              'is_required'      => $optionList->pivot->is_required ?? false,
              'display_order'    => $optionList->pivot->display_order ?? 0,
              'options'          => $optionList->options->map(function ($option) {
                  return [
                      'id'          => $option->id,
                      'name'        => $option->name,
                      'description' => $option->description,
                      'price_cents' => $option->price_cents ?? 0,
                      'is_active'   => $option->is_active,
                  ];
              })->toArray()
          ];
      })->toArray();

      $optionsWithoutList = $item->options()->whereDoesntHave('list')->get();
      if ($optionsWithoutList->isNotEmpty()) {
          $optionListsFormatted[] = [
              'list_id'          => 0,
              'list_name'        => 'Options',
              'list_description' => null,
              'min_selections'   => 0,
              'max_selections'   => null,
              'is_required'      => false,
              'display_order'    => 999,
              'options'          => $optionsWithoutList->map(function ($option) {
                  return [
                      'id'          => $option->id,
                      'name'        => $option->name,
                      'description' => $option->description,
                      'price_cents' => $option->price_cents ?? 0,
                      'is_active'   => $option->is_active,
                  ];
              })->toArray()
          ];
      }

      return $optionListsFormatted;
  }

}
