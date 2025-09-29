<?php

namespace App\Console\Commands\V1;

use App\Constants\V1\Defaults;
use App\Contracts\V1\Shared\SkuGeneratorServiceInterface;
use App\Models\V1\Store;
use App\Models\V1\User;
use App\Services\V1\StoreService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CreateStoreCommand extends Command
{
    protected $signature = 'store:create';

    protected $description = 'Créer un nouveau store avec son propriétaire';

    public function __construct(
        private readonly StoreService $storeService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🏪 Générateur de Store POS');
        $this->info('==========================');

        $storeData = $this->collectStoreInfo();
        if (! $storeData) {
            return CommandAlias::FAILURE;
        }

        $ownerData = $this->collectOwnerInfo();
        if (! $ownerData) {
            return CommandAlias::FAILURE;
        }

        $additionalData = $this->collectAdditionalInfo();

        $allData = array_merge($storeData, $additionalData, $ownerData);

        if (! $this->validateData($allData)) {
            return CommandAlias::FAILURE;
        }

        if (! $this->confirmCreation($allData)) {
            $this->info('Création annulée.');
            return CommandAlias::SUCCESS;
        }

        $store = $this->storeService->createStore($allData);

        $this->displaySuccess($store);

        return CommandAlias::SUCCESS;
    }
    private function collectStoreInfo(): ?array
    {
        $this->info("\n📋 Informations du Store:");

        $name = $this->ask('Nom du store');
        if (empty($name)) {
            $this->error('Le nom du store est obligatoire.');
            return null;
        }

        $defaultSku = app(SkuGeneratorServiceInterface::class)->generate($name, Store::class);
        $sku = $this->ask("Sku du store (URL-friendly)", $defaultSku);

        if (Store::query()->where('sku', $sku)->exists()) {
            $this->error("Le sku '$sku' existe déjà. Choisissez un autre sku.");
            $sku = $this->ask("Nouveau sku");
            if (Store::query()->where('sku', $sku)->exists()) {
                $this->error("Le sku '$sku' existe toujours. Arrêt de la création.");
                return null;
            }
        }

        return [
            'name' => $name,
            'sku'  => $sku,
        ];
    }

    private function collectOwnerInfo(): ?array
    {
        $this->info("\n👤 Informations du Propriétaire:");

        $createOwner = $this->choice('Voulez-vous créer un nouveau propriétaire ou en choisir un existant ?', ['Créer', 'Choisir'], 0);

        if ($createOwner === 'Choisir') {
            $ownerEmail = $this->ask('Email de l’utilisateur existant');
            $user       = User::where('email', $ownerEmail)->first();

            if (! $user) {
                $this->error("Aucun utilisateur trouvé avec l'email '$ownerEmail'.");
                return null;
            }

            return [
                'owner_id' => $user->id,
            ];
        }

        $ownerName = $this->ask('Nom complet du propriétaire');
        if (empty($ownerName)) {
            $this->error('Le nom du propriétaire est obligatoire.');
            return null;
        }

        $ownerEmail = $this->ask('Email du propriétaire');
        if (empty($ownerEmail)) {
            $this->error('L\'email du propriétaire est obligatoire.');
            return null;
        }

        if (User::where('email', $ownerEmail)->exists()) {
            $this->error("Un utilisateur avec l'email '$ownerEmail' existe déjà.");
            return null;
        }

        $ownerPassword = $this->secret('Mot de passe du propriétaire (laisser vide pour générer)');
        if (empty($ownerPassword)) {
            $ownerPassword = Defaults::OWNER_PASSWORD;
            $this->info("Mot de passe généré: $ownerPassword");
        }

        $firstName = $this->ask('Prénom (optionnel)');
        $lastName = $this->ask('Nom de famille (optionnel)');
        $phone = $this->ask('Téléphone (optionnel)');

        return [
            'owner' => [
                'name'       => $ownerName,
                'email'      => $ownerEmail,
                'password'   => $ownerPassword,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => $phone,
            ],
        ];
    }

    private function collectAdditionalInfo(): array
    {
        $this->info("\n⚙️  Configuration du Store:");

        $email = $this->ask('Email du store (optionnel)');
        $phone = $this->ask('Téléphone du store (optionnel)');

        $collectAddress = $this->confirm('Ajouter l\'adresse du store ?');
        $address = $city = $country = $postalCode = null;

        if ($collectAddress) {
            $address = $this->ask('Adresse');
            $city = $this->ask('Ville');
            $country = $this->ask('Pays', 'Algérie');
            $postalCode = $this->ask('Code postal');
        }

        $currency = $this->choice('Devise', ['DZD', 'EUR', 'USD'], 0);
        $language = $this->choice('Langue', ['dz', 'fr', 'ar', 'en', 'es', 'de'], 0);
        $timezone = $this->choice('Timezone', [
            'UTC',
            'Africa/Algiers',
            'Europe/Paris',
            'Europe/London',
            'America/New_York',
        ], 0);

        $taxInclusive = $this->confirm('Prix TTC (taxes incluses) ?', true);
        $defaultVatRate = $this->ask('Taux de TVA par défaut (en %)', '20.00');

        return [
            'email'            => $email,
            'phone'            => $phone,
            'address'          => $address,
            'city'             => $city,
            'country'          => $country,
            'postal_code'      => $postalCode,
            'currency'         => $currency,
            'language'         => $language,
            'timezone'         => $timezone,
            'tax_inclusive'    => $taxInclusive,
            'default_vat_rate' => floatval($defaultVatRate),
            'is_active'        => true,
        ];
    }

    private function validateData(array $data): bool
    {
        $rules = [
            'name'             => 'required|string|max:255',
            'sku'              => ['required', 'string', 'max:255', Rule::unique('stores', 'sku')],
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:500',
            'city'             => 'nullable|string|max:100',
            'country'          => 'nullable|string|max:100',
            'postal_code'      => 'nullable|string|max:20',
            'currency'         => 'nullable|string|size:3',
            'language'         => 'nullable|string|size:2',
            'timezone'         => 'nullable|string|max:50',
            'tax_inclusive'    => 'nullable|boolean',
            'default_vat_rate' => 'nullable|numeric|min:0|max:100',
            'owner.name'       => 'sometimes|required|string|max:255',
            'owner.email'      => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')],
            'owner.password'   => 'sometimes|required|string|min:6',
            'owner.first_name' => 'nullable|string|max:100',
            'owner.last_name'  => 'nullable|string|max:100',
            'owner.phone'      => 'nullable|string|max:20',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $this->error('Erreurs de validation:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("• $error");
            }
            return false;
        }

        return true;
    }

    private function confirmCreation(array $data): bool
    {
        $this->info("\n📋 Récapitulatif de la création:");

        $summary = [
            ['Store', $data['name']],
            ['Sku', $data['sku']],
            ['Email', $data['email'] ?? 'Non spécifié'],
            ['Téléphone', $data['phone'] ?? 'Non spécifié'],
            ['Adresse', $this->formatAddress($data)],
            ['Devise', $data['currency'] ?? 'EUR'],
            ['Langue', $data['language'] ?? 'fr'],
            ['Timezone', $data['timezone'] ?? 'Europe/Paris'],
            ['Prix TTC', $data['tax_inclusive'] ? 'Oui' : 'Non'],
            ['TVA par défaut', ($data['default_vat_rate'] ?? 20) . '%'],
        ];

        if (isset($data['owner'])) {
            $summary = array_merge($summary, [
                ['---', '---'],
                ['Propriétaire', $data['owner']['name']],
                ['Email propriétaire', $data['owner']['email']],
                ['Prénom', $data['owner']['first_name'] ?? 'Non spécifié'],
                ['Nom de famille', $data['owner']['last_name'] ?? 'Non spécifié'],
                ['Tél. propriétaire', $data['owner']['phone'] ?? 'Non spécifié'],
            ]);
        }

        $this->table(['Propriété', 'Valeur'], $summary);

        return $this->confirm('Créer ce store ?', true);
    }

    private function formatAddress(array $data): string
    {
        $parts = array_filter([
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? null,
        ]);

        return empty($parts) ? 'Non spécifiée' : implode(', ', $parts);
    }

    private function displaySuccess(Store $store): void
    {
        $this->info("\n✅ Store créé avec succès !");

        $this->table(
            ['Propriété', 'Valeur'],
            [
                ['ID', $store->id],
                ['Nom', $store->name],
                ['sku', $store->sku],
                ['Propriétaire', $store->owner->name],
                ['Email propriétaire', $store->owner->email],
                ['Devise', $store->currency],
                ['Statut', $store->is_active ? 'Actif' : 'Inactif'],
                ['Créé le', $store->created_at->format('d/m/Y H:i:s')],
            ]
        );

        $this->info("\n🔗 Informations de connexion:");

        if ($this->confirm('Créer un device pour ce store maintenant ?')) {
            $this->call('device:token');
        }
    }
}
