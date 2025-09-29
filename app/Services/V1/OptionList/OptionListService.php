<?php

namespace App\Services\V1\OptionList;

use App\Contracts\V1\OptionList\OptionListRepositoryInterface;
use App\Contracts\V1\OptionList\OptionListServiceInterface;
use App\DTO\V1\OptionList\CreateOptionListDTO;
use App\DTO\V1\OptionList\UpdateOptionListDTO;
use App\Exceptions\V1\OptionList\OptionListCreationException;
use App\Exceptions\V1\OptionList\OptionListDeletionException;
use App\Exceptions\V1\OptionList\OptionListUpdateException;
use App\Models\V1\OptionList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class OptionListService implements OptionListServiceInterface
{
    public function __construct(
        private OptionListRepositoryInterface $repository,
    ) {}

    public function create(CreateOptionListDTO $data): OptionList
    {
        try {
            if($this->repository->existsWhere(['name' => $data->name, 'store_id' => $data->store_id])){
                throw OptionListCreationException::nameAlreadyExists($data->name);
            }

            if($data->min_selections > $data->max_selections){
                throw OptionListCreationException::invalidSelectionLimits($data->min_selections, $data->max_selections);
            }

            /** @var OptionList $optionList */
            $optionList = $this->repository->create($data->toArray());

            Log::info("Option list créé", [
                'option_list_id' => $optionList->id,
                'store_id'       => $optionList->store_id,
            ]);

            $this->clearCache($data->store_id);
            return $optionList->load('options');
        } catch(Throwable $e){
            Log::error("Erreur lors de la création de la liste d'options", [
                'error' => $e->getMessage(),
                'data'  => $data->toArray(),
            ]);

            if($e instanceof OptionListCreationException){
                throw $e;
            }

            throw OptionListCreationException::default($e);
        }
    }

    public function update(OptionList $optionList, UpdateOptionListDTO $data): OptionList
    {
        try {
            if($data->name && $data->name !== $optionList->name){
                if($this->repository->existsWhere(['name' => $data->name, 'store_id' => $optionList->store_id])){
                    throw OptionListUpdateException::nameAlreadyExists($data->name);
                }
            }

            if(($data->min_selections !== null && $data->min_selections > $optionList->max_selections) ||
               ($data->max_selections !== null && $data->max_selections < $optionList->min_selections) ||
               ($data->min_selections !== null && $data->max_selections !== null && $data->min_selections > $data->max_selections)){
                throw OptionListUpdateException::invalidSelectionLimits(
                    $data->min_selections ?? $optionList->min_selections,
                    $data->max_selections ?? $optionList->max_selections
                );
            }

            if($optionList->isUsedInActiveItems()){
                if($data->name && $data->name !== $optionList->name){
                    throw OptionListUpdateException::cannotModifyUsedInActiveItems();
                }
                if($data->min_selections !== null && $data->min_selections !== $optionList->min_selections){
                    throw OptionListUpdateException::cannotModifyUsedInActiveItems();
                }
                if($data->max_selections !== null && $data->max_selections !== $optionList->max_selections){
                    throw OptionListUpdateException::cannotModifyUsedInActiveItems();
                }
            }

            $updatedOptionList = $this->repository->update($optionList, $data->toArray());

            Log::info("Option list mise à jour", [
                'option_list_id' => $optionList->id,
                'store_id'       => $optionList->store_id,
            ]);

            assert($updatedOptionList instanceof OptionList);
            $this->clearCache($optionList->store_id);
            $updatedOptionList->load('options');
            return $updatedOptionList;
        } catch(Throwable $e){
            Log::error("Erreur lors de la mise à jour de la liste d'options", [
                'option_list_id' => $optionList->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if($e instanceof OptionListUpdateException){
                throw $e;
            }

            throw OptionListUpdateException::default($e->getMessage());
        }
    }

    public function delete(OptionList $optionList): bool
    {
        //flemme
        try {
            $storeId = $optionList->store_id;
            $result  = $this->repository->delete($optionList);

            Log::info("Option list supprimée", [
                'option_list_id' => $optionList->id,
                'store_id'       => $storeId,
                'result'         => $result
            ]);

            $this->clearCache($storeId);
            return $result;
        } catch (Throwable $e) {
            Log::error("Erreur lors de la suppression de la liste d'options", [
                'option_list_id' => $optionList->id,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString()
            ]);

            throw OptionListDeletionException::default($e);
        }
    }

    public function list(int $storeId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->filterForPos($storeId, $filters, $perPage);
    }

    private function clearCache(int $storeId): void
    {
        Cache::tags(['option_lists'])->flush();
        Cache::forget("option_lists.store.$storeId");
    }
}
