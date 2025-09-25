<?php

namespace App\Support\Results;

use App\Models\V1\StoreMember;

class LogoutResult extends Result
{

    private ?StoreMember $storeMember;

    public function __construct(bool $success, ?string $message = null, array $errors = [], mixed $data = null, ?StoreMember $storeMember = null)
    {
        $this->storeMember = $storeMember;

        parent::__construct($success, $message, $errors, $data);
    }

    public static function invalidToken(string $message = "Token invalide"): LogoutResult
    {
        return self::failure($message, [
            'token' => ['Le token ne contient pas de store member ID valide']
        ]);
    }

    public static function alreadyLoggedOut(string $message = "Déjà déconnecté"): LogoutResult
    {
        return self::failure($message, [
            'store_member' => ['Le store member est déjà déconnecté']
        ]);
    }

    public static function failed(\Throwable $e, string $message = "Échec de la déconnexion"): LogoutResult
    {
        return self::failure($message, [
            'logout' => ['Une erreur est survenue lors de la déconnexion: ' . $e->getMessage()]
        ]);
    }

    public static function successful(StoreMember $storeMember, string $message = "Déconnexion réussie"): LogoutResult
    {
        return new self(true, $message, [], null, $storeMember);
    }

    public function getStoreMember(): ?StoreMember
    {
        return $this->storeMember;
    }

}
