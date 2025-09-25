<?php

namespace Database\Seeders;

use App\Models\V1\Option;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use App\Models\V1\User;
use Database\Factories\V1\OptionFactory;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

/**
 * C'est ici que j'arrive à comprendre un ptit peu donc votre but est d'améliorer absolument tout ce que j'ai fait et me l'expliquer
 * histoire que j'apprennes avec ce que vous avez modifié, mon but est de faire une api stable maintenable, structurée performente pour ma startup
 * c le projet de ma vie
 *
 * aidez moi sur pleins de points que j'ai pu oublié aussi
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $options = OptionFactory::new()->definition();
        Option::query()->insert($options);

//        Store::factory(100)
//            ->create()
//            ->each(function ($store){
//                assert($store instanceof Store);
//
//                /** @var User $user */
//                $user = User::factory()->create();
//
//                $store->update(['owner_id' => $user->id]);
//
//                /** @var StoreMember $owner */
//                StoreMember::factory()->owner()->create([
//                    'store_id' => $store->id,
//                    'name'     => 'Owner ' . $store->name,
//                    'pin_hash' => bcrypt('0000'),
//                    'user_id'  => $user->id
//                ]);
//
//                StoreMember::factory(2)->manager()->create([
//                    'store_id' => $store->id,
//                ]);
//
//                StoreMember::factory(5)->create([
//                    'store_id' => $store->id,
//                ]);
//            });
    }
}
