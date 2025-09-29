<?php

namespace App\Exceptions\V1\Ability;

use App\Exceptions\V1\BaseApiException;

class UnknownAbilityException extends BaseApiException
{
    protected $code             = 400;
    protected $message          = 'The specified ability is unknown.';
    protected string $errorType = "UNKNOWN_ABILITY";

    public static function fromName(string $abilityName): self
    {
        return new self("L'ability '{$abilityName}' est inconnue.")->addContext('ability', $abilityName);
    }
}
