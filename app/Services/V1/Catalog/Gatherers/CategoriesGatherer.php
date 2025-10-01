<?php

namespace App\Services\V1\Catalog\Gatherers;

use App\Models\V1\Store;
use Illuminate\Support\Collection;

final class CategoriesGatherer implements GathererInterface
{

    public function gather(Store $store): Collection
    {
        $categories = $store->categories()
            ->with([
                'items.variants',

                'items.optionLists' => function ($query) {
                    $query->withPivot([
                        'is_required', 'min_selections', 'max_selections',
                        'display_order', 'is_active'
                    ]);
                },

                'items.optionLists.options' => function ($query) {
                    $query->where('is_active', true);
                },

                'children',
                'parent',
            ])
            ->orderBy('position')
            ->get();

        return collect(['categories' => $categories]);
    }
}
