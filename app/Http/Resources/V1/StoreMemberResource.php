<?php

namespace App\Http\Resources\V1;

use App\Models\V1\StoreMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StoreMember
 */
class StoreMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'store'           => $this->store()->name ?? $this->store_id,
            'name'            => $this->name,
            'code'            => $this->code(),
            'role'            => $this->role->value,
            'userId'          => $this->user_id,
            'user'            => new UserResource($this->whenLoaded('user')),
            'isActive'        => $this->is_active,
            'meta'            => $this->meta,
            'createdAt'       => $this->created_at,
            'updatedAt'       => $this->updated_at,
            'permissions'     => $this->permissions,
            'login_count'     => $this->login_count,
            'failed_attempts' => $this->failed_attempts,
            'locked_until'    => $this->locked_until,
        ];
    }
}
