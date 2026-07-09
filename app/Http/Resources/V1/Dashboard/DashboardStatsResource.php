<?php

namespace App\Http\Resources\V1\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatsResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'orders_today'        => $this->resource['orders_today'],
            'revenue_today'       => $this->resource['revenue_today'],
            'average_order_value' => $this->resource['average_order_value'],
            'active_items'        => $this->resource['active_items'],
            'revenue_by_hour'     => $this->resource['revenue_by_hour'],
            'revenue_by_day'      => $this->resource['revenue_by_day'],
            'top_items'           => $this->resource['top_items'],
        ];
    }

}
