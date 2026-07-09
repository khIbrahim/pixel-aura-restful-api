<?php

namespace App\Repositories\V1\Printing;

use App\Contracts\V1\Printing\StorePrintSettingsRepositoryInterface;
use App\Models\V1\StorePrintSettings;
use App\Repositories\V1\BaseRepository;

class StorePrintSettingsRepository extends BaseRepository implements StorePrintSettingsRepositoryInterface
{

    public function settingsForStore(int $storeId): StorePrintSettings
    {
        /** @var StorePrintSettings */
        return $this->query()
            ->firstOrCreate([
                'store_id' => $storeId,
            ]);
    }

    public function updateForStore(int $storeId, array $payload): StorePrintSettings
    {
        $settings = $this->settingsForStore($storeId);
        $settings->update($payload);

        /** @var StorePrintSettings */
        return $settings->fresh();
    }

    public function model(): string
    {
        return StorePrintSettings::class;
    }
}
