<?php

namespace App\Exceptions\V1\Ability;

use App\Exceptions\V1\BaseApiException;

class EmptyAbilitiesConfigurationException extends BaseApiException
{

    protected string $errorType = 'EMPTY_ABILITIES_CONFIGURATION';
    protected int $statusCode   = 500;

    public static function default(): self
    {
        return new self("Le fichier de configuration des abilities est vide.");
    }

}
