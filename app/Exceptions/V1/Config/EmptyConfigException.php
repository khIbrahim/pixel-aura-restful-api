<?php

namespace App\Exceptions\V1\Config;

use App\Exceptions\V1\BaseApiException;

class EmptyConfigException extends BaseApiException
{

    protected $code             = 400;
    protected $message          = "Configuration is empty.";
    protected string $errorType = "EMPTY_ABILITIES_CONFIGURATION";

}
