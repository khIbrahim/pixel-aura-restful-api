<?php

namespace App\Console\Commands\V1;

use App\Models\V1\Store;
use App\Services\V1\Store\StoreTokenService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class GenerateStoreToken extends Command
{

    protected $signature = 'store:token';

    protected $description = 'Générer un store token';

    public function __construct(
        private readonly StoreTokenService $tokenService
    ){parent::__construct();}

    public function handle(): int
    {
        $store = $this->selectStore();
        if(! $store){
            return CommandAlias::FAILURE;
        }

        $this->info("Store sélectionné: " . $store->name . " (" . $store->sku  . ")");
        if(! $this->confirm("Confirmez-vous la création d'un nouveau store token pour le store {$store->name} ?", true)){
            $this->info("Vous avez bien annulé la création du store token");
            return CommandAlias::SUCCESS;
        }

        $this->info("Génération du store token...");
        return CommandAlias::SUCCESS;
    }

    private function selectStore(): ?Store
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if (empty($stores)){
            $this->error("Aucun store actif trouvé");
            return null;
        }

        if ($stores->count() === 1){
            return $stores->first();
        }

        $storeOptions = $stores->mapWithKeys(function (Store $store) {
            return [$store->id => $store->name . " (" . $store->sku . ")"];
        })->toArray();

        $selectedStoreId = $this->choice('Sélectionnez un store', $storeOptions);

        return $stores->firstWhere('name', explode(' (', $selectedStoreId)[0]);
    }

}
