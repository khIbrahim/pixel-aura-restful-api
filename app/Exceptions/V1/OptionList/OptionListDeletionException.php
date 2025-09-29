<?php

namespace App\Exceptions\V1\OptionList;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class OptionListDeletionException extends BaseApiException
{

    protected $code             = 401;
    protected string $errorType = 'OPTION_LIST_DELETION_ERROR';

    public static function default(?Throwable $previous = null): self
    {
        return new self("Une erreur est survenue lors de la suppression de la liste d'options.", previous: $previous);
    }

}
