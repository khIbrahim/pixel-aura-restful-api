<?php

namespace App\Contracts\V1\Dashboard;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface DashboardStatsRepositoryInterface
{

    public function countOrders(int $storeId, CarbonInterface $start, CarbonInterface $end): int;

    public function sumRevenueCents(int $storeId, CarbonInterface $start, CarbonInterface $end): int;

    public function countActiveItems(int $storeId): int;

    public function getOrdersRevenueBetween(int $storeId, CarbonInterface $start, CarbonInterface $end): Collection;

    public function getTopItems(int $storeId, CarbonInterface $start, CarbonInterface $end, int $limit = 5): Collection;

}
