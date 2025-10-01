<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\Catalog\CatalogCompilationException;
use App\Http\Controllers\Controller;
use App\Models\V1\Store;
use App\Services\V1\Catalog\CatalogCacheService;
use App\Services\V1\Catalog\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{

    public function __construct(
        private readonly CatalogService      $catalogService,
        private readonly CatalogCacheService $cacheService
    ){}

    public function compact(Request $request): JsonResponse
    {
        $store   = $request->attributes->get('store');
        $channel = $request->query('channel', 'all');

        $etag    = $this->generateEtag($store, $channel);

        if($request->header('If-None-Match') === $etag){
            return response()->json(null, 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=300')
                ->header('X-Cache-Status', 'NOT_MODIFIED'); // 5 minutes
        }

        $catalog = $this->cacheService->getCachedCatalog($store, 'compact', $channel);

        if(! $catalog){
            try {
                $catalog = $this->catalogService->compile($store);

                $this->cacheService->cacheCatalog($catalog, $store, 'compact', $channel);
            } catch(CatalogCompilationException $e){
                return response()->json([
                    'message' => $e->getMessage(),
                    'error'   => $e->getErrorType(),
                    'context' => $e->getContext()
                ], $e->getStatusCode());
            }
        }

        return response()->json($catalog)
            ->header('ETag', $etag)
            ->header('Cache-Control', 'public, max-age=300')
            ->header('X-Cache-Status', $catalog ? 'HIT' : 'MISS')
            ->header('X-Menu-Version', $store->menu_version);
    }

    public function refresh(Request $request): JsonResponse
    {
        $store   = $request->attributes->get('store');
        $channel = $request->query('channel', 'all');

        $etag    = $this->generateEtag($store, $channel);

        if($request->header('If-None-Match') === $etag){
            return response()->json(null, 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=300'); // 5 minutes
        }

        try {
            $catalog = $this->catalogService->compile($store);

            $this->cacheService->cacheCatalog($catalog, $store, 'compact', $channel);

            return response()->json($catalog)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=300')
                ->header('X-Cache-Status', 'REFRESHED');
        } catch(CatalogCompilationException $e){
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getErrorType(),
                'context' => $e->getContext()
            ], $e->getStatusCode());
        }
    }

    private function generateEtag(Store $store, string $channel): string
    {
        return sprintf(
            'catalog-v%d-%s-%s',
            $store->menu_version,
            $channel,
            substr(md5($store->updated_at), 0, 8)
        );
    }

}
