<?php

namespace App\Exceptions\V1\Option;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class OptionUpdateException extends BaseApiException
{
    protected int $statusCode   = 422;
    protected string $errorType = 'OPTION_UPDATE_ERROR';

    public static function nameAlreadyExists(string $name): self
    {
        return new self("L'option '$name' existe déjà")->addContext('name', $name);
    }

    public static function cannotChangePriceWithActiveItems(): self
    {
        return new self("Impossible de modifier le prix - option utilisée dans des articles actifs");
    }

    public static function default(?Throwable $e): self
    {
        return new self(
            "Une erreur est survenue lors de la mise à jour de l'option",
            previous: $e
        );
    }
}
