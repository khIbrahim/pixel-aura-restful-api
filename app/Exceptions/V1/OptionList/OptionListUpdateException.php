<?php

namespace App\Exceptions\V1\OptionList;

use App\Exceptions\V1\BaseApiException;

class OptionListUpdateException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'OPTION_LIST_UPDATE_ERROR';

    public static function nameAlreadyExists(string $name): self
    {
        return new self("La liste d'options '$name' existe déjà")->addContext('name', $name);
    }

    public static function invalidSelectionLimits(int $min, int $max): self
    {
        return new self("Limites de sélection invalides: min={$min}, max={$max}")
            ->addContext('min_selections', $min)
            ->addContext('max_selections', $max);
    }

    public static function cannotModifyUsedInActiveItems(): self
    {
        return new self("Impossible de modifier une liste d'options utilisée dans des articles actifs");
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la mise à jour de la liste d'options: $reason" : "Erreur lors de la mise à jour de la liste d'options";
        return new self($message);
    }
}
