<?php

namespace App\Exceptions\V1\Option;

use App\Exceptions\V1\BaseApiException;

class OptionNotFoundException extends BaseApiException
{
    protected int $statusCode = 404;
    protected string $errorType = 'option_not_found';

    public static function withId(int $id): self
    {
        return new self("Option avec l'ID $id non trouvée")->addContext('option_id', $id);
    }

    public static function withName(string $name): self
    {
        return new self("Option '$name' non trouvée")->addContext('name', $name);
    }

    public static function default(): self
    {
        return new self('Option non trouvée');
    }
}
