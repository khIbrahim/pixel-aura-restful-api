<?php
namespace App\Contracts\V1\Option;

use App\DTO\V1\Option\CreateOptionDTO;
use App\DTO\V1\Option\UpdateOptionDTO;
use App\Exceptions\V1\Option\OptionCreationException;
use App\Exceptions\V1\Option\OptionDeletionException;
use App\Exceptions\V1\Option\OptionUpdateException;
use App\Models\V1\Option;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OptionServiceInterface
{

    public function list(array $filters, int $perPage = 25): LengthAwarePaginator;

    /**
     * @throws OptionCreationException
     */
    public function create(CreateOptionDTO $data): Option;

    /**
     * @throws OptionUpdateException
     */
    public function update(Option $option, UpdateOptionDTO $data): Option;

    /**
     * @throws OptionDeletionException
     */
    public function delete(Option $option): bool;

}
