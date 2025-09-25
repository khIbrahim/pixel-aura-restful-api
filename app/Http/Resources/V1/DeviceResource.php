<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Device
 */
class DeviceResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'storeId'     => $this->store_id,
            'name'        => $this->name,
            'type'        => $this->type?->value,
            'status'      => $this->getStatusAttribute(),
            'statusColor' => $this->getStatusColorAttribute(),

            'serialNumber' => $this->serial_number,
            'isActive'     => $this->is_active,
            'isBlocked'    => $this->is_blocked,
            'blockedReason'=> $this->blocked_reason,

            'lastSeenAt'      => $this->last_seen_at?->toISOString(),
            'lastHeartbeatAt' => $this->last_heartbeat_at?->toISOString(),
            'lastKnownIp'     => $this->last_known_ip,

            'appVersion'      => $this->app_version,
            'firmwareVersion' => $this->firmware_version,
            'needsUpdate'     => $this->needs_update,

            'capabilities'    => $this->capabilities ?? [],
            'settings'        => $this->settings ?? [],
            'deviceInfo'      => $this->device_info ?? [],

            'location' => [
                'name'      => $this->location_name,
                'latitude'  => $this->latitude,
                'longitude' => $this->longitude,
            ],

            'metrics' => [
                'totalTransactions'  => $this->total_transactions,
                'uptimePercentage'   => $this->uptime_percentage,
                'avgResponseTimeMs'  => $this->avg_response_time_ms,
            ],

            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
