<?php

namespace App\Enum\V1;

use App\Exceptions\InvalidRolePrefixException;

enum StoreMemberRole: string
{

    case Owner   = 'owner';
    case Manager = 'manager';
    case Cashier = 'cashier';
    case Kitchen = 'kitchen';

    public const string OWNER_PREFIX   = 'OWN';
    public const string MANAGER_PREFIX = 'MGR';
    public const string CASHIER_PREFIX = 'EMP';
    public const string KITCHEN_PREFIX = 'KIT';

    public function getPrefix(): string
    {
        return match($this){
            self::Owner   => self::OWNER_PREFIX,
            self::Manager => self::MANAGER_PREFIX,
            self::Cashier => self::CASHIER_PREFIX,
            self::Kitchen => self::KITCHEN_PREFIX
        };
    }

    /**
     * @throws InvalidRolePrefixException
     */
    public static function fromPrefix(string $prefix): self
    {
        return match ($prefix){
            self::OWNER_PREFIX   => self::Owner,
            self::MANAGER_PREFIX => self::Manager,
            self::CASHIER_PREFIX => self::Cashier,
            self::KITCHEN_PREFIX => self::Kitchen,
            default => throw new InvalidRolePrefixException("Le préfixe de rôle '$prefix' n'est pas valide.")
        };
    }

}
