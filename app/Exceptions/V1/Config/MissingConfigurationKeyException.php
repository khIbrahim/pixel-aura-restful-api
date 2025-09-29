<?php

namespace App\Exceptions\V1\Config;

use App\Exceptions\V1\BaseApiException;

class MissingConfigurationKeyException extends BaseApiException
{
    protected $code             = 400;
    protected $message          = "Missing configuration key.";
    protected string $errorType = "MISSING_CONFIGURATION_KEY";
}
