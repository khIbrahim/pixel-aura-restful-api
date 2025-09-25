<?php
namespace App\Contracts\V1\Option;

use App\DTO\V1\Option\CreateOptionDTO;
use App\DTO\V1\Option\UpdateOptionDTO;
use App\Models\V1\Option;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OptionServiceInterface
{

    public function list(array $filters, int $perPage = 25): LengthAwarePaginator;

    public function create(CreateOptionDTO $data): Option;

    public function update(Option $option, UpdateOptionDTO $data): Option;

    public function delete(Option $option): bool;

}
