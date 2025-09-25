<?php

namespace App\Support\Import;

use App\Support\Results\ImportResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

abstract class BaseImporter implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    WithValidation,
    WithEvents
{

    protected ImportResult $importResult;
    protected float        $startTime;
    protected bool         $useBatchMode = true;
    protected int          $batchSize    = 100;
    protected Collection   $pendingBatch;
    protected bool         $logErrors    = true;
    protected int          $maxErrors    = 1000;

    public function __construct(string $filePath){
        $this->importResult = new ImportResult($filePath);
        $this->startTime    = microtime(true);
        $this->pendingBatch = collect();
    }

    public function collection(Collection $collection): void
    {
        $this->beforeImport();

        if ($this->useBatchMode){
            $this->pendingBatch = collect();
        }

        /**
         * @var int $i
         * @var Collection $row
         */
        foreach ($collection as $i => $row){
            try {
                $this->importResult->incrementTotal();

                $this->validateRow($row, $i);

                $data = $this->prepareData($row);

                if ($this->useBatchMode){
                    $this->pendingBatch->push([
                        'row'       => $row,
                        'row_index' => $i,
                        'data'      => $data,
                    ]);

                    if ($this->pendingBatch->count() >= $this->batchSize){
                        $this->processBatch();
                    }
                } else {
                    $this->processSingleRow($data, $row, $i);
                }
            } catch (Throwable $e){
                $this->handleRowError($e, $row, $i);
            }
        }

        if ($this->useBatchMode && $this->pendingBatch->isNotEmpty()){
            $this->processBatch();
        }

        $this->afterImport();
    }

    public function chunkSize(): int
    {
        return 500;
    }

    protected function afterImport(): void
    {
        $endTime       = microtime(true);
        $executionTime = ($endTime - $this->startTime) * 1000;

        $this->importResult->setProcessTimeMs((int) $executionTime);

        if ($this->importResult->getErrorCount() === 0){
            $this->importResult->markAsSuccess("import terminé avec succès.");
        } else {
            $this->importResult->setMessage("Import terminé avec " . $this->importResult->getErrorCount() . " erreurs.");
        }
    }

    protected function processBatch(): void
    {
        if($this->pendingBatch->isEmpty()){
            return;
        }

        try {
            DB::transaction(function (){
                foreach ($this->pendingBatch as $item){
                    $this->processSingleRow($item['data'], $item['row'], $item['row_index']);
                }
            }, 3);

            $this->pendingBatch = collect();
        } catch (Throwable $e){
            $this->handleBatchError($e);
        }
    }

    protected function processSingleRow(array $data, Collection $row, int $rowIndex): void
    {
        try {
            $this->processEntity($data, $row, $rowIndex);
            $this->importResult->incrementImported();
        } catch (Throwable $e){
            $errorContext = $this->getErrorContext($data);
            $errorMessage = "Erreur lors de l'importation de $errorContext: " . $e->getMessage();

            $this->importResult->incrementSkipped();
            $this->importResult->addError(
                message: $errorMessage,
                context: $row->toArray(),
                rowIndex: $rowIndex + 2,
            );

            if($this->logErrors){
                Log::error($errorMessage, [
                    'exception' => $e,
                    'row'       => $row->toArray(),
                    'rowIndex'  => $rowIndex + 2,
                    'data'      => $data,
                ]);
            }
        }
    }

    protected function handleRowError(Throwable $e, Collection $row, int $rowIndex): void
    {
        $errorMessage = "Erreur lors de l'importation de la ligne " . ($rowIndex + 2) . ": " . $e->getMessage();

        $this->importResult->incrementSkipped();

        if($this->importResult->getErrorCount() < $this->maxErrors){
            $this->importResult->addError(
                message: $errorMessage,
                context: $row->toArray(),
                rowIndex: $rowIndex + 2,
            );
        }

        if($this->logErrors){
            Log::error($errorMessage, [
                'exception' => $e,
                'row'       => $row->toArray(),
                'rowIndex'  => $rowIndex + 2,
            ]);
        }
    }

    protected function handleBatchError(Throwable $e): void
    {
        foreach ($this->pendingBatch as $item){
            $data      = $item['data'];
            $row       = $item['row'];
            $rowIndex  = $item['row_index'];

            $errorContext = $this->getErrorContext($data);
            $errorMessage = "Erreur batch pour $errorContext: " . $e->getMessage();

            $this->importResult->incrementSkipped();
            if($this->importResult->getErrorCount() < $this->maxErrors){
                $this->importResult->addError(
                    message: $errorMessage,
                    context: $row->toArray(),
                    rowIndex: $rowIndex + 2,
                );
            }
        }

        if($this->logErrors){
            Log::error("Erreur lors du traitement du batch: " . $e->getMessage(), [
                'exception' => $e,
                'batch'     => $this->pendingBatch->map(fn($item) => $item['row']->toArray())->toArray(),
            ]);
        }

        $this->pendingBatch = collect();
    }

    public function getResults(): ImportResult
    {
        return $this->importResult;
    }

    abstract protected function validateRow(Collection $row, int $rowIndex): void;
    abstract protected function prepareData(Collection $row): array;
    abstract protected function getErrorContext(array $data): string;
    abstract protected function processEntity(array $data, Collection $row, int $rowIndex): void;

    protected function beforeImport()
    {
        // Hook pour les actions avant l'import
    }

}
