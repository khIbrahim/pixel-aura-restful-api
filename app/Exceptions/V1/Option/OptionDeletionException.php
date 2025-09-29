<?php

namespace App\Exceptions\V1\Option;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class OptionDeletionException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'OPTION_DELETION_ERROR';

    public static function usedInActiveItems(): self
    {
        return new self("Impossible de supprimer une option utilisée dans des articles actifs");
    }

    public static function partOfActiveOptionList(): self
    {
        return new self("Impossible de supprimer une option faisant partie d'une liste d'options active");
    }

    public static function default(?Throwable $e): self
    {
        return new self(
            "Une erreur est survenue lors de la suppression de l'option.",
            previous: $e
        );
    }
}
