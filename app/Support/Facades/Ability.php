<?php

namespace App\Support\Facades;

use App\Support\Registry\AbilityRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool exists(string $ability)
 * @method static string canDo(string $domain, string $action)
 * @method static array validate(array $abilities)
 * @method static Collection all()
 * @method static Collection byDomain()
 * @method static array stats()
 * @method static void clearCache()
 * @method static void warmCache()
 *
 * @see AbilityRegistry
 */
class Ability extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return AbilityRegistry::class;
    }

    public static function for(string $domain, string $action): string
    {
        return static::getFacadeRoot()->canPerformAction($domain, $action);
    }

    public static function forActions(string $domain, array $actions): array
    {
        $registry = static::getFacadeRoot();
        return array_map(fn($action) => $registry->canPerformAction($domain, $action), $actions);
    }

    public const array CRUD       = ['read', 'create', 'update', 'delete'];
    public const array READ_WRITE = ['read', 'create', 'update'];
    public const array READ_ONLY  = ['read'];
    public const array MANAGEMENT = ['read', 'create', 'update', 'delete', 'manage'];

}
