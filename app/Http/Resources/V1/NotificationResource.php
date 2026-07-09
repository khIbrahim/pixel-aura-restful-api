<?php

namespace App\Http\Resources\V1;

use App\Models\V1\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'store_id'       => $this->store_id,
            'event_id'       => $this->event_id,
            'type'           => $this->type,
            'title'          => $this->title,
            'message'        => $this->message,
            'severity'       => $this->severity?->value ?? $this->severity,
            'subject_type'   => $this->subject_type,
            'subject_id'     => $this->subject_id,
            'subject_number' => $this->subject_number,
            'action_url'     => $this->action_url,
            'data'           => $this->data,
            'read_at'        => $this->read_at?->toISOString(),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }

}
