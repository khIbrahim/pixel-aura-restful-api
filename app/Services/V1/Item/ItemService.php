<?php

namespace App\Services\V1\Item;

use App\Contracts\V1\Ingredient\IngredientRepositoryInterface;
use App\Contracts\V1\Item\ItemAttachmentServiceInterface;
use App\Contracts\V1\Item\ItemRepositoryInterface;
use App\Contracts\V1\Item\ItemServiceInterface;
use App\Contracts\V1\ItemVariant\ItemVariantServiceInterface;
use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\DTO\V1\Ingredient\IngredientPivotDTO;
use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Item\UpdateItemDTO;
use App\DTO\V1\Media\MediaUploadOptions;
use App\DTO\V1\Option\OptionPivotDTO;
use App\Events\V1\Item\ItemCreated;
use App\Events\V1\Item\ItemDeleted;
use App\Events\V1\Item\ItemUpdated;
use App\Exceptions\V1\Item\ItemCreationException;
use App\Exceptions\V1\Item\ItemDeletionException;
use App\Exceptions\V1\Item\ItemUpdateException;
use App\Models\V1\Item;
use App\Services\V1\Media\MediaManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class ItemService implements ItemServiceInterface
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository,
        private IngredientRepositoryInterface $ingredientRepository,
        private OptionRepositoryInterface $optionRepository,
        private MediaManager $mediaManager,
        private ItemAttachmentServiceInterface $itemAttachmentService,
        private ItemVariantServiceInterface $itemVariantService,
    ) {}

    /**
     * @throws ItemCreationException
     */
    public function create(CreateItemDTO $data): Item
    {
        try {
            return DB::transaction(function () use ($data) {
                /** @var Item $item */
                $item = $this->itemRepository->create($data->toArray());

                if (! empty($data->variants)) {
                    $this->itemVariantService->bulkCreateVariants($item, $data->variants);
                }

                foreach ($data->ingredients as $ingredientDTO) {
                    $ingredient = $this->ingredientRepository->findOrCreateIngredient($ingredientDTO);
                    $this->itemAttachmentService->attachIngredient($item, IngredientPivotDTO::fromCreation($ingredientDTO, $ingredient));
                }

                foreach ($data->options as $optionDTO) {
                    $option = $this->optionRepository->findOrCreateOption($optionDTO);
                    $this->itemAttachmentService->attachOption($item, OptionPivotDTO::fromCreation($optionDTO, $option));
                }

                if ($data->image !== null && $data->image !== '') {
                    $this->mediaManager->uploadImage($item, $data->image, MediaUploadOptions::fromItemImage());
                }

                Log::info('Item créé', [
                    'item_id' => $item->id,
                    'store_id' => $item->store_id,
                    'created_by' => $data->created_by,
                ]);

                broadcast(new ItemCreated($item))->toOthers();
                $this->clearCache($data->store_id);

                return $item->load(['variants', 'ingredients', 'options', 'category', 'tax']);
            });
        } catch (QueryException $e) {
            Log::error("Erreur lors de la création de l'item", [
                'error' => $e->getMessage(),
                'store_id' => $data->store_id,
                'created_by' => $data->created_by,
            ]);

            throw ItemCreationException::queryError($e);
        } catch(Throwable $e){
            if($e instanceof ItemCreationException){
                throw $e;
            }

            throw ItemCreationException::default($e);
        }
    }

    /**
     * @throws ItemUpdateException
     */
    public function update(UpdateItemDTO $data, Item $item): Item
    {
        try {
            return DB::transaction(function () use ($data, $item) {
                /** @var Item $item */
                $item = $this->itemRepository->update($item, $data->toArray());

                Log::info('Item mis à jour', [
                    'item_id'    => $item->id,
                    'store_id'   => $item->store_id,
                    'updated_by' => $data->updated_by,
                ]);

                broadcast(new ItemUpdated($item))->toOthers();
                $this->clearCache($item->store_id);

                return $item->load(['category', 'tax', 'creator', 'variants', 'ingredients', 'options']);
            });
        } catch (QueryException $e) {
            Log::error("Erreur lors de la mise à jour de l'item", [
                'error' => $e->getMessage(),
                'item_id' => $item->id,
                'store_id' => $item->store_id,
                'updated_by' => $data->updated_by,
            ]);

            throw ItemUpdateException::queryError($e);
        } catch(Throwable $e){
            if($e instanceof ItemUpdateException){
                throw $e;
            }

            throw ItemUpdateException::default($e);
        }
    }

    /**
     * @throws ItemDeletionException
     */
    public function delete(Item $item): bool
    {
        try {
            return DB::transaction(function () use ($item) {
                if($item->hasVariants()){
                    throw ItemDeletionException::hasVariants();
                }

                $storeId = $item->store_id;

                $this->itemAttachmentService->detachAllIngredients($item);
                $this->itemAttachmentService->detachAllOptions($item);
                $this->itemAttachmentService->detachAllOptionLists($item);
                $this->itemVariantService->deleteAllVariants($item);

                $this->mediaManager->deleteImage($item, 'main_image');
                $this->mediaManager->deleteImage($item, 'gallery');

                $deleted = $this->itemRepository->delete($item);

                if ($deleted) {
                    Log::info('Item supprimé', [
                        'item_id'  => $item->id,
                        'store_id' => $storeId,
                        'name'     => $item->name,
                    ]);

                    broadcast(new ItemDeleted($item->id, $item->store_id))->toOthers();
                    $this->clearCache($storeId);
                }

                if(! $deleted){
                    throw ItemDeletionException::default(null);
                }

                return true;
            });
        } catch (QueryException $e) {
            Log::error("Erreur lors de la suppression de l'item", [
                'error' => $e->getMessage(),
                'item_id' => $item->id,
                'store_id' => $item->store_id,
            ]);

            throw ItemDeletionException::queryError($e);
        } catch(Throwable $e){
            if($e instanceof ItemDeletionException){
                throw $e;
            }

            throw ItemDeletionException::default($e);
        }
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->itemRepository->list($storeId, $filters, $perPage);
    }

    private function clearCache(int $storeId): void
    {
        Cache::tags(['items'])->flush();
        Cache::forget('items.store.'.$storeId);
    }
}
