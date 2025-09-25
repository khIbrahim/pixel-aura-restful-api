<?php

namespace App\Contracts\V1\Export;

use App\DTO\V1\StoreMember\ExportStoreMembersDTO;
use App\Support\Results\ExportResult;

interface StoreMemberExportServiceInterface
{

    public function exportSync(int $storeId, ExportStoreMembersDTO $data): ExportResult;

    public function exportAsync(int $storeId, ExportStoreMembersDTO $data, ?int $priority = null): array;

    public function getExportResult(string $jobId): ?ExportResult;

}
