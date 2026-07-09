<?php

namespace App\Models\V1;

use App\Enum\V1\Notification\NotificationSeverity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int                  $id
 * @property      int                  $store_id
 * @property      null|string          $event_id
 * @property      string               $title
 * @property      string               $type
 * @property      string               $message
 * @property      NotificationSeverity $severity
 * @property      null|string          $subject_type
 * @property      null|int             $subject_id
 * @property      null|string          $subject_number
 * @property      null|string          $action_url
 * @property      array                $data
 * @property      null|Carbon          $read_at
 * @property      null|Carbon          $created_at
 * @property      null|Carbon          $updated_at
 */
class Notification extends Model
{
    use HasTimestamps;

    protected $table = 'notifications';

    protected $fillable = [
        'store_id',
        'event_id',
        'type',
        'title',
        'message',
        'severity',
        'subject_type',
        'subject_id',
        'subject_number',
        'action_url',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data'     => 'array',
        'read_at'  => 'datetime',
        'severity' => NotificationSeverity::class,
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

}
