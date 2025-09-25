<?php

namespace App\Http\Resources\V1;

use App\Models\V1\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email
        ];
    }

}
