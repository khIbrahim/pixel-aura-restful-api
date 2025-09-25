<?php

namespace App\Jobs\V1;

use App\Events\V1\Import\ImportCompleted;
use App\Events\V1\Import\ImportFailed;
use App\Support\Import\StoreMember\StoreMemberAsyncImport;
use App\Support\Results\ImportResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessImportJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries     = 3;
    public array $backoff = [60, 300, 600];

    private string $importerClass;
    private string $filePath;
    private array  $parameters;
    private int    $storeId;

    private ?int $priority = null;

    public function __construct(string $importerClass, string $filePath, array $parameters, int $storeId)
    {
        $this->importerClass = $importerClass;
        $this->filePath      = $filePath;
        $this->parameters    = $parameters;
        $this->storeId       = $storeId;
    }

    public function uniqueId(): string
    {
        return md5(sprintf('%s-%s-%d',
            $this->filePath,
            $this->importerClass,
            $this->parameters['storeId']
        ));
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        Log::info("Début du job d'importation", [
            'importer'   => $this->importerClass,
            'file_path'  => $this->filePath,
            'parameters' => $this->parameters,
            'store_id'   => $this->storeId,
        ]);

        try {
            /** @var StoreMemberAsyncImport $importer */
            $importer = $this->createImporter();

            Excel::import($importer, $this->filePath);

            $results = $importer->getResults();

            $this->processResults($results);

            Event::dispatch(new ImportCompleted($results, $this->storeId));
        } catch(Throwable $e){
            Log::error("Échec du job d'importation", [
                'error'    => $e->getMessage(),
                'importer' => $this->importerClass,
                'file'     => $this->filePath,
                'store_id' => $this->storeId
            ]);

            Event::dispatch(new ImportFailed($this->importerClass, $e->getMessage(), $this->storeId));

            throw $e;
        } finally {
            $this->cleanupFile();
        }
    }

    private function processResults(ImportResult $resultDTO): void
    {
        Log::info('Importation terminée', [
            'success'     => $resultDTO->isSuccess(),
            'imported'    => $resultDTO->getImported(),
            'skipped'     => $resultDTO->getSkipped(),
            'error_count' => $resultDTO->getErrorCount(),
            'store_id'    => $this->storeId
        ]);
    }

    private function createImporter()
    {
        return new $this->importerClass(...array_values($this->parameters));
    }

    private function cleanupFile(): void
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
            Log::info("Fichier d'importation supprimé", ['file' => $this->filePath]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Échec du job d'importation", [
            'error'    => $exception->getMessage(),
            'importer' => $this->importerClass,
            'file'     => $this->filePath,
            'store_id' => $this->storeId
        ]);

        $this->cleanupFile();
    }

    public function getJobId(): ?string
    {
        return $this->job?->getJobId();
    }
}
