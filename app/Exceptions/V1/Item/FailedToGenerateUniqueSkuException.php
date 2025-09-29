<?php

namespace App\Exceptions\V1\Item;

use App\Exceptions\V1\BaseApiException;

class FailedToGenerateUniqueSkuException extends BaseApiException
{
    protected $code = 500;
    protected string $errorType = 'FAILED_TO_GENERATE_UNIQUE_SKU';
}
