<?php

namespace App\Services\V1\Catalog;

use App\Exceptions\V1\Catalog\CatalogCompilationException;
use App\Models\V1\Store;
use App\Services\V1\Catalog\Formatters\CompactFormatter;
use App\Services\V1\Catalog\Gatherers\GathererInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CatalogService
{

    /** @var GathererInterface[] */
    private array $gatherers;

    public function __construct(
        private readonly CompactFormatter $compactFormatter
    ){
        $this->gatherers['store']      = new Gatherers\StoreGatherer();
        $this->gatherers['categories'] = new Gatherers\CategoriesGatherer();
    }

    /**
     * @throws CatalogCompilationException
     */
    public function compile(Store $store, string $format = 'compact'): array
    {
        $startTime = microtime(true);

        Log::info("Début de la compilation du catalog", [
            'store_id'     => $store->id,
            'menu_version' => $store->menu_version,
            'format'       => $format,
        ]);

        try {
            $rawData  = $this->gatherData($store);
            $result   = $this->compactFormatter->format($rawData);
            $duration = microtime(true) - $startTime;

            Log::info("Catalog compilé avec succès", [
                'store_id'         => $store->id,
                'format'           => $format,
                'duration_ms'      => round($duration * 1000, 2),
                'categories_count' => count($result['categories']),
                'memory_peak_mb'   => round(memory_get_peak_usage() / 1024 / 1024, 2),
            ]);

            if ($duration > 2.0) {
                Log::warning("La compilation du catalogue a pris plus de 2 secondes", [
                    'store_id' => $store->id,
                    'duration_seconds' => $duration,
                ]);
            }

            return $result;

        } catch (Throwable $e) {
            Log::error("échec de la compilation du catalog", [
                'store_id' => $store->id,
                'format'   => $format,
                'error'    => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
            ]);

            throw CatalogCompilationException::default($e);
        }
    }

    private function gatherData(Store $store): Collection
    {
        $data = collect();
        foreach($this->gatherers as $gatherer){
            $rawData = $gatherer->gather($store);
            $data = $data->merge($rawData);
        }

        $data['store'] = $store;

        return $data;
    }

}
