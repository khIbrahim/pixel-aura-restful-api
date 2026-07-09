<?php

namespace App\Enum\V1\Notification;

enum NotificationSeverity: string
{

    case Info    = "info";
    case Success = "success";
    case Warning = "warning";
    case Error   = "error";

}
