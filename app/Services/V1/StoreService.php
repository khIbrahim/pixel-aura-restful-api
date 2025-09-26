<?php

namespace App\Services\V1;

use App\Constants\V1\Defaults;
use App\Contracts\V1\Store\StoreRepositoryInterface;
use App\Contracts\V1\Store\StoreServiceInterface;
use App\DTO\V1\Store\CreateStoreDTO;
use App\Enum\StoreMemberRole;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use App\Models\V1\User;
use App\Services\V1\StoreMember\StoreMemberCodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

readonly class StoreService implements StoreServiceInterface
{

    public function __construct(
        private StoreRepositoryInterface $storeRepository
    ){}

    public function create(CreateStoreDTO $data): Store
    {
        /** @var Store $store */
        $store = $this->storeRepository->create(collect($data->toArray())->except('owner')->toArray());
        return $store;
    }

    /**
     * @param array $data
     * @return Store
     */
    public function createStore(array $data): Store
    {
        return DB::transaction(function () use ($data) {
            $owner = $this->createOrFindOwner($data);

            $storeData = collect($data)->except('owner')->toArray();
            $storeData['owner_id'] = $owner->id;

            /** @var Store $store */
            $store = Store::create($storeData);
            $this->createOwnerMember($store, $owner);

            return $store->load('owner');
        });
    }

    public function createOrFindOwner(array $data): User
    {
        if(! isset($data['owner'])){
            return User::create([
                'name'              => Defaults::defaultOwnerName((string) $data['name']),
                'email'             => (string) ($data['email'] ?? $data['slug'] . '@temp.local'),
                'password'          => Hash::make(Defaults::OWNER_PASSWORD),
                'email_verified_at' => now(),
                'code'              => 1
            ]);
        }

        $ownerData = $data['owner'];
        $nameExplode = explode(" ", (string) $ownerData['name']);
        $firstName   = $nameExplode[0] ?? null;
        $lastName    = $nameExplode[1] ?? null;
        return User::create([
            'name'              => (string) $ownerData['name'],
            'email'             => (string) $ownerData['email'],
            'password'          => Hash::make((string) $ownerData['password']),
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'phone'             => $ownerData['phone'] ?? null,
            'email_verified_at' => now(),
            'code'              => 1
        ]);
    }

    private function createOwnerMember(Store $store, User $owner): void
    {
        $codeService = app(StoreMemberCodeService::class);
        $code = $codeService->next($store->id, StoreMemberRole::Owner);

        StoreMember::create([
            'store_id'  => $store->id,
            'user_id'   => $owner->id,
            'name'      => 'Owner',
            'code'      => $code,
            'role'      => StoreMemberRole::Owner->value,
            'pin_hash'  => Hash::make(Defaults::PIN),
            'is_active' => true,
            'pin_last_changed_at' => now(),
            'permissions' => ['*'],
        ]);
    }

}
