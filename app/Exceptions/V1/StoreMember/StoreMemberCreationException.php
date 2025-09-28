<?php

namespace App\Exceptions\V1\StoreMember;

use App\Exceptions\V1\BaseApiException;

class StoreMemberCreationException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'store_member_creation_failed';

    public static function codeAlreadyExists(string $code): self
    {
        return new self("Le code membre '{$code}' existe déjà")
            ->addContext('code', $code);
    }

    public static function cannotCreateOwner(): self
    {
        return new self("Impossible de créer un propriétaire via cette méthode")
            ->setStatusCode(403)
            ->setErrorType('forbidden');
    }

    public static function invalidRole(string $role): self
    {
        return new self("Le rôle '{$role}' n'est pas valide")
            ->addContext('role', $role);
    }

    public static function default(string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la création du membre: {$reason}" : "Erreur lors de la création du membre";
        return new self($message);
    }
}
