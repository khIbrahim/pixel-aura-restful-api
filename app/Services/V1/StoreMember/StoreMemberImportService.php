<?php

namespace App\Services\V1\StoreMember;

use App\Jobs\V1\ProcessImportJob;
use App\Support\Import\StoreMember\StoreMemberAsyncImport;
use App\Support\Import\StoreMember\StoreMemberSyncImport;
use App\Support\Results\ImportResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StoreMemberImportService
{

    public function importSync(UploadedFile $file, int $storeId, bool $useBatchMode = true, ?int $batchSize = null): ImportResult {
        $filePath = $this->storeFile($file);

        $importer = new StoreMemberSyncImport(
            filePath: $filePath,
            storeId: $storeId,
            codeService: app(StoreMemberCodeService::class),
            permissionsService: app(StoreMemberPermissionsService::class),
            options: [
                'useBatchMode' => $useBatchMode,
                'batchSize'    => $batchSize,
            ]
        );

        try {
            Excel::import($importer, $filePath);

            return $importer->getResults();
        } finally {
            $this->cleanupFile($filePath);
        }
    }

    public function importAsync(
        UploadedFile $file,
        int          $storeId,
        bool         $useBatchMode = true,
        int          $batchSize = 100,
        int          $priority = 1
    ): array {
        $filePath = $this->storeFile($file);

        $job = new ProcessImportJob(
            importerClass: StoreMemberAsyncImport::class,
            filePath: $filePath,
            parameters: [
                'filePath'           => $filePath,
                'storeId'            => $storeId,
                'codeService'        => app(StoreMemberCodeService::class),
                'permissionsService' => app(StoreMemberPermissionsService::class),
                'options'            => [
                    'useBatchMode'   => $useBatchMode,
                    'batchSize'      => $batchSize,
                ]
            ],
            storeId: $storeId
        );

        if ($priority !== null) {
            $job->setPriority($priority);
        }

        dispatch($job);

        return [
            'status'    => 'processing',
            'message'   => 'Import en cours de traitement',
            'job_id'    => $job->getJobId(),
            'file_path' => $filePath,
        ];
    }

    private function storeFile(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        $fileName = Str::uuid() . '.' . $extension;

        $path = Storage::disk('local')->putFileAs('imports/temp', $file, $fileName);

        return storage_path('app/' . $path);
    }

    private function cleanupFile(string $filePath): void
    {
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
}
