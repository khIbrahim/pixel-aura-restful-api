<?php

namespace App\Exceptions\V1\StoreMember;

use App\Exceptions\V1\BaseApiException;

class StoreMemberUpdateException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'store_member_update_failed';

    public static function cannotUpdateOwner(): self
    {
        return new self("Impossible de modifier le propriétaire du magasin")
            ->setStatusCode(403)
            ->setErrorType('forbidden');
    }

    public static function cannotChangeRole(): self
    {
        return new self("Impossible de modifier le rôle de ce membre")
            ->setStatusCode(403)
            ->setErrorType('forbidden');
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la mise à jour du membre: {$reason}" : "Erreur lors de la mise à jour du membre";
        return new self($message);
    }
}
