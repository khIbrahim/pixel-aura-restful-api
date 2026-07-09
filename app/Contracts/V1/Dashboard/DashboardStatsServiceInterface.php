<?php

namespace App\Contracts\V1\Dashboard;

interface DashboardStatsServiceInterface
{

    public function statsGrid(int $storeId, string $currency, string $timezone): array;

}
