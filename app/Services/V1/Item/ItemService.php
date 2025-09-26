<?php

namespace App\Services\V1\Item;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\Contracts\V1\Item\ItemAttachmentServiceInterface;
use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\Contracts\V1\Item\ItemServiceInterface;
use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\DTO\V1\Ingredient\IngredientPivotDTO;
use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Media\MediaUploadOptions;
use App\DTO\V1\Option\OptionPivotDTO;
use App\Events\V1\Item\ItemCreated;
use App\Exceptions\V1\Item\ItemCreationException;
use App\Models\V1\Item;
use App\Services\V1\Media\MediaManager;
use App\Support\Results\MediaResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class ItemService implements ItemServiceInterface
{

    public function __construct(
        private ItemRepositoryInterface $itemRepository,
        private IngredientRepositoryInterface $ingredientRepository,
        private OptionRepositoryInterface $optionRepository,
        private MediaManager            $mediaManager,
        private ItemAttachmentServiceInterface $itemAttachmentService,
    ){}

    /**
     * @throws ItemCreationException
     */
    public function create(CreateItemDTO $data): Item
    {
        try {
            return DB::transaction(function() use ($data) {
                /** @var Item $item */
                $item = $this->itemRepository->create($data->toArray());

                if(! empty($data->variants)){
                    $this->itemRepository->bulkCreateVariants($item, $data->variants);
                }

                foreach ($data->ingredients as $ingredientDTO) {
                    $ingredient = $this->ingredientRepository->findOrCreateIngredient($ingredientDTO);
                    $this->itemAttachmentService->attachIngredient($item, IngredientPivotDTO::fromCreation($ingredientDTO, $ingredient));
                }

                foreach ($data->options as $optionDTO) {
                    $option = $this->optionRepository->findOrCreateOption($optionDTO);
                    $this->itemAttachmentService->attachOption($item, OptionPivotDTO::fromCreation($optionDTO, $option));
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
                $this->clearCache($data->store_id);

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

    private function clearCache(int $storeId): void
    {
        Cache::tags(['items'])->flush();
        Cache::forget('items.store.' . $storeId);
    }
}
