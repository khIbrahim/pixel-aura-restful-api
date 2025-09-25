<?php

namespace App\Constants\V1;

final class AuthenticateStatus
{

    public const string INACTIVE   = 'inactive_member';
    public const string PIN        = 'invalid_pin';
    public const string RATE_LIMIT = 'rate_limited';
    public const string LOCKED     = 'account_locked';
    public const string SUCCESS    = 'successful_authentication';

}
