<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Auth\PersonalAccessToken;
use App\Models\V1\Device;
use App\Models\V1\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        /** @var Store $store */
        $store  = $this->resource['store'];
        /** @var Device $device */
        $device = $this->resource['device'];
        /** @var PersonalAccessToken $token */
        $token  = $this->resource['token'];

        return [
            'store'  => new StoreResource($store),
            'device' => new DeviceResource($device),
            'token'  => [
                'id'         => $token->id,
                'abilities'  => $token->abilities,
                'expiresAt'  => $token->expires_at?->toISOString(),
                'lastUsedAt' => $token->last_used_at?->toISOString(),
                'createdAt'  => $token->created_at?->toISOString(),
            ],
        ];
    }
}
