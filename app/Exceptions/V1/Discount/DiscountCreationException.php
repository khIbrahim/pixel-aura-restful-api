<?php

namespace App\Exceptions\V1\Discount;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class DiscountCreationException extends BaseApiException
{

    protected int $statusCode   = 400;
    protected string $errorType = "DISCOUNT_CREATION_ERROR";

    public static function codeNotUnique(string $code): self
    {
        return new self("Le code de remise '$code' n'est pas unique.");
    }

    public static function default(?Throwable $e): self
    {
        return new self("Une erreur est survenue lors de la création de la remise.", previous: $e);
    }

}
