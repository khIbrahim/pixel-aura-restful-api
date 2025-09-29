<?php

namespace App\Services\V1\Ingredient;

use App\Contracts\V1\Option\OptionRepositoryInterface;
use App\Contracts\V1\Option\OptionServiceInterface;
use App\DTO\V1\Option\CreateOptionDTO;
use App\DTO\V1\Option\UpdateOptionDTO;
use App\Exceptions\V1\Option\OptionCreationException;
use App\Exceptions\V1\Option\OptionDeletionException;
use App\Exceptions\V1\Option\OptionUpdateException;
use App\Models\V1\Option;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class OptionService implements OptionServiceInterface
{

    public function __construct(
        private OptionRepositoryInterface $optionRepository,
    ){}

    public function list(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->optionRepository->list($filters, $perPage);
    }

    public function create(CreateOptionDTO $data): Option
    {
        try {
            if($this->optionRepository->existsWhere(['name' => $data->name])){
                throw OptionCreationException::nameAlreadyExists($data->name);
            }

            /** @var Option $option */
            $option = $this->optionRepository->create($data->toArray());

            Log::info("Option créée", [
                'option_id' => $option->id,
                'store_id'  => $option->store_id,
                'name'      => $option->name
            ]);

            return $option;
        } catch(Throwable $e){
            Log::error("Erreur lors de la création de l'option", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if($e instanceof OptionCreationException){
                throw $e;
            }

            throw OptionCreationException::default($e);
        }
    }

    public function update(Option $option, UpdateOptionDTO $data): Option
    {
        try {
            if($option->name !== $data->name && $this->optionRepository->existsWhere(['name' => $data->name, 'store_id' => $option->store_id])){
                throw OptionUpdateException::nameAlreadyExists($data->name);
            }

            if($option->price_cents !== $data->price_cents && $option->hasActiveItems()){
                throw OptionUpdateException::cannotChangePriceWithActiveItems();
            }

            /** @var Option $option */
            $option = $this->optionRepository->update($option, $data->toArray());

            Log::info("Option mise à jour", [
                'option_id' => $option->id,
                'store_id'  => $option->store_id,
                'name'      => $option->name
            ]);

            return $option;
        } catch(Throwable $e){
            Log::error("Erreur lors de la mise à jour de l'option", [
                'option_id' => $option->id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString()
            ]);

            if($e instanceof OptionUpdateException){
                throw $e;
            }

            throw OptionUpdateException::default($e);
        }
    }

    public function delete(Option $option): bool
    {
        try {
            if($option->hasActiveItems()){
                throw OptionDeletionException::usedInActiveItems();
            }

            if($option->isInActiveOptionList()){
                throw OptionDeletionException::partOfActiveOptionList();
            }

            $option->items()->detach($option);

            $deleted = $this->optionRepository->delete($option);

            Log::info("Option supprimée", [
                'option_id' => $option->id,
                'store_id'  => $option->store_id,
                'name'      => $option->name
            ]);

            return $deleted;
        } catch(Throwable $e){
            Log::error("Erreur lors de la suppression de l'option", [
                'option_id' => $option->id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString()
            ]);

            if($e instanceof OptionDeletionException){
                throw $e;
            }

            throw OptionDeletionException::default($e);
        }
    }
}
