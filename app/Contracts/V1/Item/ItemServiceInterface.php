<?php

namespace App\Contracts\V1\Item;

use App\DTO\V1\Item\CreateItemDTO;
use App\DTO\V1\Option\OptionsAttachDTO;
use App\Models\V1\Item;
use App\Models\V1\Option;
use App\Support\Results\MediaResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface ItemServiceInterface
{

    public function create(CreateItemDTO $data): Item;

    public function uploadImage(Item $item, UploadedFile|string $file, string $collection): MediaResult;

    public function list(int $storeId, array $filters = [], int $perPage = 25) : LengthAwarePaginator;

    /**
     * @return Option[]
     */
    public function attachOptions(Item $item): array;

}
