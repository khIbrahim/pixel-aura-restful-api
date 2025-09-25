<?php

namespace App\Constants\V1;

final class Defaults
{

    public const string OWNER_PASSWORD = 'Password123!';
    public const string PIN            = '0000';
    public const bool ACTIVE           = true;
    public const array META            = [];

    public static function defaultOwnerName(string $storeName): string
    {
        return 'Propriétaire de ' . $storeName;
    }

}
