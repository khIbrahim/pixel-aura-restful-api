<?php

namespace App\Exceptions\V1\StoreMember;

use App\Exceptions\V1\BaseApiException;

class InvalidRolePrefixException extends BaseApiException
{

    protected string $errorType = "INVALID_ROLE_PREFIX";
    protected int $statusCode   = 400;
    protected $message          = "Prefix du rôle invalide.";

}
