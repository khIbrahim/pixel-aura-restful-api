<?php

namespace App\Exceptions\V1\StoreMember;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class StoreMemberDeletionException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'STORE_MEMBER_DELETION_ERROR';

    public static function cannotDeleteOwner(): self
    {
        return new self("Impossible de supprimer le propriétaire du magasin")
            ->setStatusCode(403)
            ->setErrorType('forbidden');
    }

    public static function cannotDeleteSelf(): self
    {
        return new self("Impossible de supprimer son propre compte")
            ->setStatusCode(403)
            ->setErrorType('forbidden');
    }

    public static function hasActiveOrders(): self
    {
        return new self("Impossible de supprimer un membre ayant des commandes actives");
    }

    public static function default(?Throwable $e): self
    {
        return new self(
            "Une erreur est survenue lors de la suppression du membre du magasin",
            previous: $e
        );
    }
}
