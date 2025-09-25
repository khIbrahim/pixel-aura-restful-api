<?php

namespace App\Support\Export\StoreMember;

use App\Events\V1\Export\ExportCompleted;
use App\Support\Results\ExportResult;
use Illuminate\Support\Facades\Cache;

class StoreMemberAsyncExport extends StoreMemberSyncExport
{

    private string $jobId;
    private string $outputPath;
    private string $format;

    public function __construct(
        int     $storeId,
        string  $format,
        array   $filters,
        string  $outputPath = '',
        ?string $jobId = null,
    ){
        parent::__construct($storeId, $filters);

        $this->jobId      = $jobId ?? '';
        $this->outputPath = $outputPath;
        $this->format     = $format;
    }

    public function onExportCompleted(int $storeId): void
    {
        if(! $this->jobId){
            return;
        }

        $result = new ExportResult(
            success: true,
            message: "Export complété avec succès",
            filePath: $this->outputPath,
            format: $this->format,
            recordCount: $this->recordCount,
            processedTimeMs: $this->getProcessTimeMs(),
            jobId: $this->jobId,
            downloadUrl: route('store-members.exports.download', [
                'store' => $storeId,
                'jobId' => $this->jobId
            ]),
        );

        Cache::put('export_result_' . $this->jobId, $result, now()->addDay());

        ExportCompleted::dispatch($result, $storeId);
    }

}
