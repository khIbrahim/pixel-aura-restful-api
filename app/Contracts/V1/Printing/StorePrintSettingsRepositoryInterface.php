<?php

namespace App\Contracts\V1\Printing;

use App\Contracts\V1\Base\BaseRepositoryInterface;
use App\Models\V1\StorePrintSettings;

interface StorePrintSettingsRepositoryInterface extends BaseRepositoryInterface
{

    public function settingsForStore(int $storeId): StorePrintSettings;

    public function updateForStore(int $storeId, array $payload): StorePrintSettings;

}
