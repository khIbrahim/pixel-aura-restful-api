<?php

namespace App\Services\V1\StoreMember;

use App\Contracts\V1\Export\StoreMemberExportServiceInterface;
use App\DTO\V1\StoreMember\ExportStoreMembersDTO;
use App\Jobs\V1\ProcessExportJob;
use App\Support\Export\StoreMember\StoreMemberAsyncExport;
use App\Support\Export\StoreMember\StoreMemberSyncExport;
use App\Support\Results\ExportResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class StoreMemberExportService implements StoreMemberExportServiceInterface
{

    public function exportSync(int $storeId, ExportStoreMembersDTO $data): ExportResult
    {
        $outputPath = $this->generateOutputPath($storeId, $data->format);

        $exporter = new StoreMemberSyncExport(
            storeId: $storeId,
            filters: $data->filters,
        );

        try {
            Excel::store($exporter, $outputPath, 'local');

            $recordCount   = $exporter->getRecordCount();
            $processedTime = $exporter->getProcessTimeMs();

            return ExportResult::success(
                message: "Export complété avec succès",
                data: [
                    'filePath'        => $outputPath,
                    'format'          => $data->format,
                    'recordCount'     => $recordCount,
                    'processedTimeMs' => $processedTime
                ]
            );
        } catch (Throwable $e){
            return ExportResult::error(
                message: "Erreur lors de l'export des membres du store",
                format: $data->format,
                errors: ['exception' => $e->getMessage()]
            );
        }
    }

    public function exportAsync(int $storeId, ExportStoreMembersDTO $data, ?int $priority = null): array
    {
        $jobId      = (string) Str::uuid();
        $outputPath = $this->generateOutputPath($storeId, $data->format);

        $job = new ProcessExportJob(
            exporterClass: StoreMemberAsyncExport::class,
            parameters: [
                'storeId'    => $storeId,
                'format'     => $data->format,
                'filters'    => $data->filters,
                'outputPath' => $outputPath
            ],
            storeId: $storeId,
            jobId: $jobId
        );

        if($priority !== null){
            $job->setPriority($priority);
        }

        dispatch($job);

        return [
            'message' => 'Export des membres du store en cours',
            'data'    => [
                'jobId'    => $jobId,
                'filePath' => $outputPath,
                'format'   => $data->format,
            ]
        ];
    }

    public function getExportResult(string $jobId): ?ExportResult
    {
        return Cache::get('export_result_' . $jobId);
    }

    protected function generateOutputPath(int $storeId, string $format, ?string $jobId = null): string
    {
        $date = date('d-m-Y');
        $jobIdPart = $jobId ? "job_{$jobId}_" : '';

        return "exports/$date/store_{$storeId}_members$jobIdPart.$format";
    }

}
