<?php

namespace App\Exceptions\V1\OptionList;

use App\Exceptions\V1\BaseApiException;

class OptionListNotFoundException extends BaseApiException
{
    protected int $statusCode   = 404;
    protected string $errorType = 'option_list_not_found';

    public static function withId(int $id): self
    {
        return new self("Liste d'options avec l'ID $id non trouvée")->addContext('option_list_id', $id);
    }

    public static function withName(string $name): self
    {
        return new self("Liste d'options '$name' non trouvée")->addContext('name', $name);
    }

    public static function default(): self
    {
        return new self('Liste d\'options non trouvée');
    }
}
