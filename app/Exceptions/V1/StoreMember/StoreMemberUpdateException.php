<?php

namespace App\Exceptions\V1\StoreMember;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class StoreMemberUpdateException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'STORE_MEMBER_UPDATE_ERROR';

    public static function default(?Throwable $e): self
    {
        return new self(
            "Une erreur est survenue lors de la mise à jour du membre du magasin",
            previous: $e
        );
    }
}
