<?php

namespace Database\Factories\V1;

use App\Constants\V1\StoreTokenAbilities;
use App\Enum\StoreMemberRole;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use App\Services\V1\StoreMember\StoreMemberCodeService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<StoreMember>
 */
class StoreMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'store_id'            => Store::factory(), // relation, pas d’ID ici
            'name'                => $this->faker->name(),
            'role'                => StoreMemberRole::Cashier->value,
            'pin_hash'            => Hash::make(mt_rand(1000, 9999)),
            'pin_last_changed_at' => now(),
            'permissions'         => StoreTokenAbilities::getAbilitiesByRole(StoreMemberRole::Cashier),
            'is_active'           => (bool) random_int(0, 1),
            'meta'                => [],
            'code_number'         => null,
        ];
    }

    public function configure(): StoreMemberFactory|Factory
    {
        return $this->afterCreating(function (StoreMember $storeMember) {
            $storeMember->update([
                'code_number' => (app(StoreMemberCodeService::class))->next($storeMember->store->id, $storeMember->role)
            ]);
        });
    }

    public function withCode(): self
    {
        return $this->state(function (array $attributes) {
            $store = Store::findOrFail($attributes['store_id'])?->first();
            $role  = StoreMemberRole::from($attributes['role']);
            $code  = app(StoreMemberCodeService::class)->next($store->id, $role);
            return ['code_number' => $code];
        });
    }

    public function owner(): self
    {
        return $this->state(fn() => ['role' => StoreMemberRole::Owner->value, 'permissions'=> StoreTokenAbilities::getAbilitiesByRole(StoreMemberRole::Owner)]);
    }

    public function manager(): self
    {
        return $this->state(fn() => ['role' => StoreMemberRole::Manager->value, 'permissions'=> StoreTokenAbilities::getAbilitiesByRole(StoreMemberRole::Manager)]);
    }

    public function kitchen(): self
    {
        return $this->state(fn() => ['role' => StoreMemberRole::Kitchen->value, 'permissions'=> StoreTokenAbilities::getAbilitiesByRole(StoreMemberRole::Kitchen)]);
    }

    public function cashier(): self
    {
        return $this->state(fn() => ['role' => StoreMemberRole::Cashier->value, 'permissions'=> StoreTokenAbilities::getAbilitiesByRole(StoreMemberRole::Cashier)]);
    }
}
