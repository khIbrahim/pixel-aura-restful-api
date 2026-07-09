<?php

namespace App\Services\V1\Dashboard;

use App\Contracts\V1\Dashboard\DashboardStatsRepositoryInterface;
use App\Contracts\V1\Dashboard\DashboardStatsServiceInterface;
use Carbon\CarbonImmutable;

class DashboardStatsService implements DashboardStatsServiceInterface
{

    public function __construct(
        private readonly DashboardStatsRepositoryInterface $repository
    ){}

    public function statsGrid(int $storeId, string $currency, string $timezone): array
    {
        $todayStart = CarbonImmutable::now($timezone)->startOfDay()->utc();
        $todayEnd   = CarbonImmutable::now($timezone)->endOfDay()->utc();

        $yesterdayStart = CarbonImmutable::now($timezone)->subDay()->startOfDay()->utc();
        $yesterdayEnd   = CarbonImmutable::now($timezone)->endOfDay()->utc();

        $ordersToday     = $this->repository->countOrders($storeId, $todayStart, $todayEnd);
        $ordersYesterday = $this->repository->countOrders($storeId, $yesterdayStart, $yesterdayEnd);

        $revenueTodayCents     = $this->repository->sumRevenueCents($storeId, $todayStart, $todayEnd);
        $revenueYesterdayCents = $this->repository->sumRevenueCents($storeId, $yesterdayStart, $yesterdayEnd);

        $averageOrderValueTodayCents     = $ordersToday > 0 ? (int) round($revenueTodayCents / $ordersToday) : 0;
        $averageOrderValueYesterdayCents = $ordersYesterday > 0 ? (int) round($revenueYesterdayCents / $ordersYesterday) : 0;

        return [
            'orders_today'        => [
                'value'          => $ordersToday,
                'previous_value' => $ordersYesterday,
                'change_percent' => $this->changePercent($ordersToday, $ordersYesterday),
            ],

            'revenue_today'       => [
                ...$this->money($revenueTodayCents, $currency),
                'previous'       => $this->money($revenueYesterdayCents, $currency),
                'change_percent' => $this->changePercent($revenueTodayCents, $revenueYesterdayCents),
            ],

            'average_order_value' => [
                ...$this->money($averageOrderValueTodayCents, $currency),
                'previous'       => $this->money($averageOrderValueYesterdayCents, $currency),
                'change_percent' => $this->changePercent($averageOrderValueTodayCents, $averageOrderValueYesterdayCents),
            ],

            'active_items'        => [
                'value' => $this->repository->countActiveItems($storeId),
            ],

            'revenue_by_hour'     => $this->revenueByHour(
                storeId:   $storeId,
                start:     $todayStart,
                end:       $todayEnd,
                currency:  $currency,
                timezone:  $timezone
            ),

            'revenue_by_day'      => $this->revenueByDay($storeId, $currency, $timezone),
            'top_items'           => $this->topItems($storeId, $currency, $timezone),
        ];
    }

    private function revenueByHour(int $storeId, CarbonImmutable $start, CarbonImmutable $end, string $currency, string $timezone): array
    {
        $hours = collect(range(0, 23))
            ->mapWithKeys(fn(int $hour) => [$hour => 0]);

        $orders = $this->repository->getOrdersRevenueBetween($storeId, $start, $end);

        foreach($orders as $order){
            $hour = $order->created_at->timezone($timezone)->hour;

            $hours[$hour] += (int) $order->total_cents;
        }

        return $hours
            ->map(function(int $revenueCents, int $hour) use ($currency) {
                return [
                    'hour'    => str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . 'h',
                    'revenue' => $this->money($revenueCents, $currency),
                ];
            })
            ->values()
            ->all();
    }

    private function revenueByDay(int $storeId, string $currency, string $timezone): array
    {
        $start = CarbonImmutable::now($timezone)->subDays(6)->startOfDay()->utc();
        $end   = CarbonImmutable::now($timezone)->endOfDay()->utc();

        $days = collect(range(6, 0))
            ->mapWithKeys(function(int $daysAgo) use ($timezone) {
                $date = CarbonImmutable::now($timezone)->subDays($daysAgo);

                return [
                    $date->format('Y-m-d') => [
                        'label' => $date->locale('fr')->isoFormat('ddd'),
                        'minor' => 0,
                    ],
                ];
            });

        $orders = $this->repository->getOrdersRevenueBetween($storeId, $start, $end);

        foreach($orders as $order){
            $key = $order->created_at->timezone($timezone)->format('Y-m-d');

            if($days->has($key)){
                $current = $days->get($key);
                $current['minor'] += (int) $order->total_cents;

                $days->put($key, $current);
            }
        }

        return $days
            ->map(fn(array $day) => [
                'date'    => ucfirst($day['label']),
                'revenue' => $this->money($day['minor'], $currency),
            ])
            ->values()
            ->all();
    }

    private function topItems(int $storeId, string $currency, string $timezone): array
    {
        $start = CarbonImmutable::now($timezone)->subDays(6)->startOfDay()->utc();
        $end   = CarbonImmutable::now($timezone)->endOfDay()->utc();

        return $this->repository->getTopItems($storeId, $start, $end)
            ->map(fn($item) => [
                'id'            => $item->item_id !== null ? (int) $item->item_id : null,
                'name'          => $item->name ?? 'Produit inconnu',
                'quantity_sold' => (int) $item->quantity_sold,
                'revenue'       => $this->money((int) $item->revenue_cents, $currency),
            ])
            ->values()
            ->all();
    }

    private function money(int $minor, string $currency): array
    {
        return [
            'minor'     => $minor,
            'formatted' => number_format($minor / 100, 2, ',', ' ') . ' ' . $currency,
            'currency'  => $currency,
        ];
    }

    private function changePercent(int|float $current, int|float $previous): float
    {
        if($previous == 0){
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

}
