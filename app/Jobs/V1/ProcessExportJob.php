<?php

namespace App\Jobs\V1;

use App\Events\V1\Export\ExportFailed;
use App\Support\Export\StoreMember\StoreMemberAsyncExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessExportJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    private int $tries = 3;
    private array $backoff = [60, 120, 240];

    private string $exporterClass;
    private array $parameters;
    private int $storeId;
    private string $jobId;

    private ?int $priority = null;

    public function __construct(string $exporterClass, array $parameters, int $storeId, string $jobId)
    {
        $this->exporterClass = $exporterClass;
        $this->storeId       = $storeId;
        $this->jobId         = $jobId;
        $this->parameters    = $parameters;
    }

    public function uniqueId(): string
    {
        return sprintf("%s-%s-%d", $this->jobId, $this->exporterClass, $this->storeId);
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Execute the job.
     * @throws Throwable
     */
    public function handle(): void
    {
        Log::info("Début de l'export", [
            'exporter'   => $this->exporterClass,
            'parameters' => $this->parameters,
            'storeId'    => $this->storeId,
            'jobId'      => $this->jobId,
        ]);

        try {
            $exporter = $this->createExporterInstance();

            Excel::store($exporter, $this->parameters['outputPath'], 'local');

            $exporter->onExportCompleted($this->storeId);

            Log::info("Fin de l'export", [
                'exporter'        => $this->exporterClass,
                'parameters'      => $this->parameters,
                'storeId'         => $this->storeId,
                'jobId'           => $this->jobId,
                'recordCount'     => $exporter->getRecordCount(),
                'processedTimeMs' => $exporter->getProcessTimeMs(),
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de l'export", [
                'exporter'   => $this->exporterClass,
                'parameters' => $this->parameters,
                'storeId'    => $this->storeId,
                'jobId'      => $this->jobId,
                'error'      => $e->getMessage(),
            ]);

            ExportFailed::dispatch($this->storeId, $e->getMessage(), $this->exporterClass, $this->jobId);

            throw $e;
        }
    }

    /** @return StoreMemberAsyncExport */
    public function createExporterInstance(): StoreMemberAsyncExport
    {
        $parameters = array_merge($this->parameters, ['jobId' => $this->jobId]);

        return new $this->exporterClass(
            $parameters['storeId'],
            $parameters['format'] ?? 'csv',
            $parameters['filters'] ?? [],
            $parameters['outputPath'] ?? '',
            $parameters['jobId']
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Échec du job d'exportation", [
            'error'    => $exception->getMessage(),
            'exporter' => $this->exporterClass,
            'job_id'   => $this->jobId,
            'store_id' => $this->storeId
        ]);

        ExportFailed::dispatch($this->storeId, $exception->getMessage(), $this->exporterClass, $this->jobId);
    }
}
