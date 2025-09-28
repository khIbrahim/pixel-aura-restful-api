<?php

namespace App\Exceptions\V1\Option;

use App\Exceptions\V1\BaseApiException;

class OptionDeletionException extends BaseApiException
{
    protected int $statusCode = 422;
    protected string $errorType = 'option_deletion_failed';

    public static function usedInActiveItems(): self
    {
        return new self("Impossible de supprimer une option utilisée dans des articles actifs");
    }

    public static function partOfActiveOptionList(): self
    {
        return new self("Impossible de supprimer une option faisant partie d'une liste d'options active");
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la suppression de l'option: $reason" : "Erreur lors de la suppression de l'option";
        return new self($message);
    }
}
