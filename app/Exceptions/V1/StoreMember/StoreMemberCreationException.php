<?php

namespace App\Exceptions\V1\StoreMember;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class StoreMemberCreationException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'STORE_MEMBER_CREATION_ERROR';

    public static function codeAlreadyExists(string $code): self
    {
        return new self("Le code membre '$code' existe déjà")
            ->addContext('code', $code);
    }

    public static function cannotCreateOwner(): self
    {
        return new self("Impossible de créer un propriétaire via cette méthode")
            ->setStatusCode(403)
            ->setErrorType('forbidden');
    }

    public static function default(?Throwable $e): self
    {
        return new self("Une erreur est survenue lors de la création du membre", previous: $e);
    }
}
