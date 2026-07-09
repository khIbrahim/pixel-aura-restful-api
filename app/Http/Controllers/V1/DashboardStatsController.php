<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Dashboard\DashboardStatsServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Dashboard\DashboardStatsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardStatsController extends Controller
{

    public function __construct(
        private readonly DashboardStatsServiceInterface $service
    ){}

    public function stats(Request $request): JsonResponse
    {
        $store = $request->attributes->get('store');

        $stats = $this->service->statsGrid(
            storeId:  (int) $store->id,
            currency: $store->currency ?? 'DZD',
            timezone: $store->timezone ?? config('app.timezone')
        );

        return response()->json([
            'data' => new DashboardStatsResource($stats),
        ]);
    }

}
