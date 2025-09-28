<?php

namespace App\Exceptions\V1\Option;

use App\Exceptions\V1\BaseApiException;

class OptionUpdateException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'option_update_failed';

    public static function nameAlreadyExists(string $name): self
    {
        return new self("L'option '$name' existe déjà")->addContext('name', $name);
    }

    public static function cannotChangePriceWithActiveItems(): self
    {
        return new self("Impossible de modifier le prix - option utilisée dans des articles actifs");
    }

    public static function default(?string $reason = null): self
    {
        $message = $reason ? "Erreur lors de la mise à jour de l'option: $reason" : "Erreur lors de la mise à jour de l'option";
        return new self($message);
    }
}
