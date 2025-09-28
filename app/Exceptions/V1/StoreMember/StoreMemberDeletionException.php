<?php

namespace App\Exceptions\V1\StoreMember;

use App\Exceptions\V1\BaseApiException;

class StoreMemberDeletionException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'store_member_deletion_failed';

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

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la suppression du membre: $reason" : "Erreur lors de la suppression du membre";
        return new self($message);
    }
}
