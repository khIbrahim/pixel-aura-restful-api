<?php

namespace App\Exceptions\V1\StoreMember;

use App\Exceptions\V1\BaseApiException;

class StoreMemberNotFoundException extends BaseApiException
{
    protected int $statusCode = 404;
    protected string $errorType = 'store_member_not_found';

    public static function withId(int $id): self
    {
        return new self("Membre du magasin avec l'ID {$id} non trouvé")->addContext('store_member_id', $id);
    }

    public static function withCode(string $code): self
    {
        return new self("Membre du magasin avec le code '{$code}' non trouvé")->addContext('code', $code);
    }

    public static function default(): self
    {
        return new self('Membre du magasin non trouvé');
    }
}
