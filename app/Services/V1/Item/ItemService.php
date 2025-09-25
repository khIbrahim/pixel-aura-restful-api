<?php

namespace App\Services\V1\Item;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\Contracts\V1\Item\ItemServiceInterface;
use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Media\MediaUploadOptions;
use App\Events\V1\Item\ItemCreated;
use App\Exceptions\V1\Item\ItemCreationException;
use App\Models\V1\Item;
use App\Services\V1\Media\MediaManager;
use App\Support\Results\MediaResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class ItemService implements ItemServiceInterface
{

    public function __construct(
        private ItemRepositoryInterface $itemRepository,
        private IngredientRepositoryInterface $ingredientRepository,
        private OptionRepositoryInterface $optionRepository,
        private MediaManager            $mediaManager,
    ){}

    /**
     * @throws ItemCreationException
     */
    public function create(CreateItemDTO $data): Item
    {
        try {
            return DB::transaction(function() use ($data) {
                $item = $this->itemRepository->createItem($data);

                if(! empty($data->variants)){
                    $this->itemRepository->bulkCreateVariants($item, $data->variants);
                }

                foreach ($data->ingredients as $ingredientDTO) {
                    $ingredient = $this->ingredientRepository->findOrCreateIngredient($ingredientDTO);
                    $this->itemRepository->attachIngredient($item, $ingredient, $ingredientDTO);
                }

                foreach ($data->options as $optionDTO) {
                    $option = $this->optionRepository->findOrCreateOption($optionDTO);
                    $this->itemRepository->attachOption($item, $option, $optionDTO);
                }

                if ($data->image !== null && $data->image !== ''){
                    $this->uploadImage($item, $data->image, 'main_image');
                }

                Log::info("Item créé", [
                    'item_id'    => $item->id,
                    'store_id'   => $item->store_id,
                    'created_by' => $data->created_by,
                ]);

                broadcast(new ItemCreated($item))->toOthers();

                return $item->load(['variants','ingredients','options','category','tax']);
            });
        } catch (QueryException $e){
            Log::error("Erreur lors de la création de l'item", [
                'error'      => $e->getMessage(),
                'store_id'   => $data->store_id,
                'created_by' => $data->created_by,
            ]);

            throw ItemCreationException::queryError($e);
        }
    }

    public function uploadImage(Item $item, string|UploadedFile $file, string $collection): MediaResult
    {
        $options = MediaUploadOptions::fromItemImage($collection);

        return $this->mediaManager->uploadImage($item, $file, $options);
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->itemRepository->list($storeId, $filters, $perPage);
    }

//    public function attachOptions(Item $item, OptionsAttachDTO $optionsData): array
//    {
//        DB::transaction(function () use ($item, $optionsData) {
//            $options =
//        })
//    }
    public function attachOptions(Item $item): array
    {
        return [];
    }
}
