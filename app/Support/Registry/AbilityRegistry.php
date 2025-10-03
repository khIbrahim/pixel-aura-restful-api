<?php

namespace App\Support\Registry;

use App\Enum\V1\StoreMemberRole;
use App\Exceptions\V1\Ability\EmptyAbilitiesConfigurationException;
use App\Exceptions\V1\Ability\UnknownAbilityException;
use App\Exceptions\V1\Config\MissingConfigurationKeyException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AbilityRegistry
{

    private const string ALL_ABILITIES_KEY       = 'allAbilities';
    private const string ABILITIES_BY_DOMAIN_KEY = 'abilities_by_domain';

    private const string CACHE_KEY_PREFIX = 'abilities.registry.v1';
    private const int    CACHE_TTL        = 3600;

    private ?array $config = null;

    /**
     * @throws MissingConfigurationKeyException
     * @throws EmptyAbilitiesConfigurationException
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    public function getAllAbilities(): Collection
    {
        return $this->getCachedData(self::CACHE_KEY_PREFIX . self::ALL_ABILITIES_KEY, function (){
            return collect($this->config['abilities'] ?? []);
        });
    }

    public function getRoleAbilities(StoreMemberRole $role): Collection
    {
        $cacheKey = 'role.' . $role->value;

        return $this->getCachedData($cacheKey, function () use ($role) {
            $roleConfig = $this->config['roles'][$role->value] ?? [];
            return $this->expandAbilities($roleConfig);
        });
    }

    public function getGroupAbilities(string $group): Collection
    {
        return $this->getCachedData("grup.$group", function () use ($group) {
            $groupConfig = $this->config['groups'][$group] ?? [];
            return $this->expandAbilities($groupConfig);
        });
    }

    public function getAbilitiesByDomain(): Collection
    {
        return $this->getCachedData(self::ABILITIES_BY_DOMAIN_KEY, function (){
            $abilities = $this->getAllAbilities();
            $domains   = [];

            foreach ($abilities as $ability => $desc){
                $domain = explode(".", $ability)[0];
                if(! isset($domains[$domain])){
                    $domains[$domain] = [];
                }

                $domains[$domain][$ability] = $desc;
            }

            return collect($domains);
        });
    }

    /**
     * @throws UnknownAbilityException
     */
    public function canPerformAction(string $domain, string $action): string
    {
        $ability = "$domain.$action";

        if(! $this->abilityExists($ability)){
            throw UnknownAbilityException::fromName($ability);
        }

        return $ability;
    }

    /**
     * @throws MissingConfigurationKeyException
     * @throws EmptyAbilitiesConfigurationException
     */
    private function loadConfig(): void
    {
        $this->config = config('abilities', []);

        if (empty($this->config)){
            throw EmptyAbilitiesConfigurationException::default();
        }

        $this->validateConfig();
    }

    /**
     * @throws MissingConfigurationKeyException
     */
    private function validateConfig(): void
    {
        $required = ['abilities', 'roles', 'groups'];

        foreach ($required as $key) {
            if (! array_key_exists($key, $this->config)) {
                throw new MissingConfigurationKeyException("La clé de configuration '$key' est manquante dans le fichier des abilities.");
            }
        }
    }

    public function getCachedData(string $key, callable $resolver): Collection
    {
        $cacheConfig = $this->config['expose']['cache'] ?? ['enabled' => false];

        if(! ($cacheConfig['enabled'] ?? false)){
            return collect($resolver());
        }

        $fullKey = self::CACHE_KEY_PREFIX . ".$key";
        $ttl     = $cacheConfig['ttl'] ?? self::CACHE_TTL;

        return Cache::remember($fullKey, $ttl, function () use ($resolver){
            return collect($resolver());
        });
    }

    public function clearCache(): void
    {
        $pattern = self::CACHE_KEY_PREFIX . '.*';

        //On utilise Redis
        if (config('cache.default') === 'redis'){
            $keys = Cache::getRedis()->keys($pattern);
            if (! empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } else {
            $knownKeys = [
                self::ALL_ABILITIES_KEY,
                self::ABILITIES_BY_DOMAIN_KEY
            ];

            foreach (StoreMemberRole::cases() as $role){
                $knownKeys[] = 'role.' . $role->value;
            }

            foreach ($this->config['groups'] ?? [] as $groupName => $_){
                $knownKeys[] = 'group.' . $groupName;
            }

            foreach ($knownKeys as $key){
                $fullKey = self::CACHE_KEY_PREFIX . ".$key";
                Cache::forget($fullKey);
            }
        }
    }

    public function abilityExists(string $ability): bool
    {
        return $this->getAllAbilities()->has($ability);
    }

    public function expandAbilities(array $items): Collection
    {
        $expanded = [];
        $stack    = $items;

        while(! empty($stack)){
            $item = array_shift($stack);

            if(is_string($item) && str_starts_with($item, '@')){
                $groupName  = substr($item, 1);
                $groupItems = $this->config['groups'][$groupName] ?? [];

                foreach ($groupItems as $groupItem){
                    $stack[] = $groupItem;
                }

                continue;
            }

            if (is_string($item) && $this->abilityExists($item)) {
                $expanded[] = $item;
            }
        }

        return collect(array_unique($expanded))->sort()->values();
    }

    public function warmCache(): void
    {
        $this->getAllAbilities();
        $this->getAbilitiesByDomain();

        foreach (StoreMemberRole::cases() as $role){
            $this->getRoleAbilities($role);
        }

        foreach ($this->config['groups'] ?? [] as $groupName => $_){
            $this->getGroupAbilities($groupName);
        }
    }

    public function getStats(): array
    {
        return [
            'total_abilities' => $this->getAllAbilities()->count(),
            'total_groups'    => count($this->config['groups']),
            'total_roles'     => count($this->config['roles']),
            'version'         => $this->config['version'] ?? 1,
            'domains'         => $this->getAbilitiesByDomain()->keys()->toArray()
        ];
    }

}
