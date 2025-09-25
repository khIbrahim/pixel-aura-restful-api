<?php

namespace App\Services\V1\Auth;

use App\Enum\StoreMemberRole;
use App\Models\V1\StoreMember;
use App\Support\Registry\AbilityRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class AbilityManager
{

    private const int PERMISSION_CACHE_TTL = 300;

    public function __construct(
        private readonly AbilityRegistry $registry
    ){
        $this->registry->warmCache();
    }

    public function hasAbility(StoreMember $storeMember, string $ability): bool
    {
        if(! $this->registry->abilityExists($ability)){
            return false;
        }

        $cacheKey = $this->getPermissionCacheKey($storeMember->id, $ability);

        return Cache::remember($cacheKey, self::PERMISSION_CACHE_TTL, function () use ($storeMember, $ability){
           return $this->checkAbilityInternal($storeMember, $ability);
        });
    }

    public function hasAllAbilities(StoreMember $storeMember, array $abilities): bool
    {
        return array_all($abilities, fn($ability) => $this->hasAbility($storeMember, $ability));
    }

    public function hasAnyAbility(StoreMember $storeMember, array $abilities): bool
    {
        return array_any($abilities, fn($ability) => $this->hasAbility($storeMember, $ability));
    }

    public function getMissingAbilities(StoreMember $storeMember, array $requiredAbilities): Collection
    {
        $memberAbilities = $this->getMemberAbilities($storeMember);

        return collect($requiredAbilities)
            ->reject(fn($ability) => $memberAbilities->contains($ability))
            ->values();
    }

    public function validateDomainAccess(StoreMember $storeMember, string $domain, array $requiredActions = ['read']): array
    {
        $results = [];

        foreach ($requiredActions as $action) {
            $ability          = $this->registry->canPerformAction($domain, $action);
            $results[$action] = $this->hasAbility($storeMember, $ability);
        }

        return $results;
    }

    public function getPermissionReport(StoreMember $storeMember): array
    {
        $memberAbilities = $this->getMemberAbilities($storeMember);
        $domainAbilities = $this->registry->getAbilitiesByDomain();

        $report = [
            'member_id'       => $storeMember->id,
            'role'            => $storeMember->role->value,
            'total_abilities' => $memberAbilities->count(),
            'domains'         => []
        ];

        foreach ($domainAbilities as $domain => $abilities) {
            $domainReport = [
                'domain'            => $domain,
                'total_abilities'   => count($abilities),
                'granted_abilities' => 0,
                'abilities'         => []
            ];

            foreach ($abilities as $ability => $description) {
                $hasAbility = $memberAbilities->contains($ability);
                $domainReport['abilities'][$ability] = [
                    'description' => $description,
                    'granted'     => $hasAbility
                ];

                if ($hasAbility) {
                    $domainReport['granted_abilities']++;
                }
            }

            $domainReport['coverage_percentage'] = round(
                ($domainReport['granted_abilities'] / $domainReport['total_abilities']) * 100,
                2
            );

            $report['domains'][$domain] = $domainReport;
        }

        return $report;
    }

    public function clearMemberCache(StoreMember $storeMember): void
    {
        $patterns = [
            "member_abilities.$storeMember->id.*",
            "store_member:$storeMember->id:permission:*"
        ];

        foreach ($patterns as $pattern) {
            if (config('cache.default') === 'redis') {
                $keys = Cache::getRedis()->keys($pattern);
                if (! empty($keys)) {
                    Cache::getRedis()->del($keys);
                }
            }
        }
    }

    public function warmMemberCache(StoreMember $storeMember): void
    {
        $this->getMemberAbilities($storeMember);

        $commonAbilities = [
            'store.read', 'order.read', 'menu.read', 'item.read',
            'customer.read', 'members.auth', 'members.logout'
        ];

        foreach ($commonAbilities as $ability) {
            if ($this->registry->abilityExists($ability)) {
                $this->hasAbility($storeMember, $ability);
            }
        }
    }

    private function checkAbilityInternal(StoreMember $storeMember, string $ability): bool
    {
        if ($storeMember->role === StoreMemberRole::Owner){
            return true;
        }

        $memberAbilities = $this->getMemberAbilities($storeMember);
        if($memberAbilities->contains($ability)){
            return true;
        }

        return array_any($memberAbilities->toArray(), fn($memberAbility) => $this->matchesWildcard($memberAbility, $ability));
    }

    private function matchesWildcard(string $pattern, string $ability): bool
    {
        if ($pattern === "*") {
            return true;
        }

        if (str_ends_with($pattern, '*')) {
            $domain = substr($pattern, 0, -2);
            return str_starts_with($ability, $domain . '.');
        }

        return false;
    }

    private function getMemberAbilities(StoreMember $storeMember): Collection
    {
        $cacheKey = "member_abilities.$storeMember->id.{$storeMember->updated_at->timestamp}";

        return Cache::remember($cacheKey, self::PERMISSION_CACHE_TTL, function () use ($storeMember) {
            $roleAbilities   = $this->registry->getRoleAbilities($storeMember->role);
            $customAbilities = $storeMember->permissions ?? [];

            return $roleAbilities->merge($customAbilities)->unique()->sort()->values();
        });
    }

    private function getPermissionCacheKey(int $storeMemberId, string $ability): string
    {
        return "store_member:$storeMemberId:ability:$ability";
    }

    public function getUsageStats(): array
    {
        return [
            'cache_hit_rate'         => 'N/A', // TODO instrumentation
            'most_checked_abilities' => [], // TODO logging
            'registry_stats'         => $this->registry->getStats()
        ];
    }

}
