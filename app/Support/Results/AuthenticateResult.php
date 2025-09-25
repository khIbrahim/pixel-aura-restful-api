<?php

namespace App\Support\Results;

use App\Models\V1\StoreMember;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;

class AuthenticateResult extends Result
{

    public ?StoreMember $storeMember {
        get {
            return $this->storeMember;
        }
    }

    public function __construct(
        bool         $success,
        ?string      $message = null,
        array        $errors = [],
        mixed        $data = null,
        ?StoreMember $storeMember = null
    ){
        parent::__construct($success, $message, $errors, $data);

        $this->storeMember = $storeMember;
    }

    public static function successfulAuthentication(
        StoreMember $storeMember,
        string $message = "Authentification réussie",
        mixed $data = null
    ): AuthenticateResult
    {
        return new self(
            success: true,
            message: $message,
            data: $data,
            storeMember: $storeMember
        );
    }

    public static function invalidCredentials(string $message = "Identifiants invalides"): AuthenticateResult
    {
        return new self(
            success: false,
            message: $message,
            errors: ['credentials' => ['Identifiants invalides']]
        );
    }

    public static function invalidPin(string $message = "PIN invalide"): AuthenticateResult
    {
        return new self(
            success: false,
            message: $message,
            errors: ['pin' => ['Pin invalide']]
        );
    }

    public static function inactiveMember(string $message = "Membre inactif"): AuthenticateResult
    {
        return new self(
            success: false,
            message: $message,
            errors: ['member' => ['Ce membre est actuellement inactif']]
        );
    }

    public static function invalidCode(string $message = "Code invalide"): AuthenticateResult
    {
        return new self(
            success: false,
            message: $message,
            errors: ['code' => ['Code invalide']]
        );
    }

    public static function rateLimitExceeded(int $retryAfter, string $message = "Trop de tentatives, veuillez réessayer plus tard"): AuthenticateResult
    {
        $formatted = CarbonInterval::seconds($retryAfter)->cascade()->forHumans([
            'join' => true,
            'parts' => 2,
            'short' => true,
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
        ]);
        return new self(
            success: false,
            message: $message,
            errors: ['rate_limit' => ['Trop de tentatives, veuillez réessayer dans ' . $formatted]],
            data: [
                'retry_after' => $retryAfter
            ]
        );
    }

    public static function accountLocked(int $secondsRemaining, string $message = "Compte vérouillé"): AuthenticateResult
    {
        $formatted = CarbonInterval::seconds($secondsRemaining)->cascade()->forHumans([
            'join' => true,
            'parts' => 2,
            'short' => true,
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
        ]);
        return new self(
            success: false,
            message: $message,
            errors: ['member' => ["Compte vérouillé, trop de tentatives d'authentification échouées, réessayez dans $formatted"]],
            data: [
                'locked_for_seconds' => $secondsRemaining
            ]
        );
    }

    public static function alreadyAuthenticated(StoreMember $storeMember, string $message = "Vous êtes déjà authentifié"): AuthenticateResult
    {
        return new self(
            success: true,
            message: $message,
            storeMember: $storeMember
        );
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'store_member_id' => $this->storeMember->id ?? null,
        ]);
    }

}
