<?php

namespace App\Console\Commands\V1;

use App\Enum\V1\DeviceType;
use App\Models\V1\Store;
use App\Services\V1\DeviceTokenService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\Console\Command\Command as CommandAlias;

class GenerateDeviceToken extends Command
{
    protected $signature = 'device:token';

    protected $description = 'Générer un device token pour un store';

    public function __construct(
        private readonly DeviceTokenService $tokenService
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $store = $this->selectStore();
        if(! $store){
            return CommandAlias::FAILURE;
        }

        $this->info("Store sélectionné: " . $store->name . " (" . $store->sku  . ")");

        $deviceType = $this->selectDeviceType();
        if(! $deviceType){
            return CommandAlias::FAILURE;
        }

        $this->info("Device sélectionné: " . $deviceType->getDisplayName());

        $defaultName = $deviceType->getDisplayName() . " #" . ($store->devices()->where('type', $deviceType->value)->count() + 1);
        $name = $this->ask("Entrez le nom à accorder pour le device", $defaultName);
        $this->info("Nom sélectionné: " . $name);

        $fingerprint = $this->ask("Entrez l'empreinte matérielle du device");
        if(empty($fingerprint)){
            $fingerprint = Str::uuid()->toString();
            $this->info("Uuid généré par " . Str::class . "::uuid()->toString(): " . $fingerprint);
        } else {
            $this->info("Fingerprint sélectionné: " . $fingerprint);
        }

        if(! $this->confirmCreation($store, $deviceType, $name, $fingerprint)){
            $this->info("Vous avez bien annulé la création du device token");
            return CommandAlias::SUCCESS;
        }

        $result = $this->tokenService->createDeviceToken(
            $store,
            $deviceType,
            $fingerprint,
            $name
        );

        $this->info("\nLe device token a bien été créé");

        $this->table(
            ['Property', 'Value'],
            [
                ['Store', $store->name],
                ['Device ID', $result['device_id']],
                ['Token', $result['token']],
                ['Expire Dans', $result['expires_at']],
                ['Abilities', implode(', ', $result['abilities'])],
            ]
        );

        $this->warn("\nNotes:");
        $this->line("• Vous ne devez pas partager ce token en plain text.");
        $this->line("• Vous ne devez pas partager le fingerprint.");
        $this->line("• Le token expirera le: {$result['expires_at']}");

        $this->info("\nAjoutez dans votre fichier .env:");
        $this->line("STORE_SKU=$store->sku");
        $this->line("DEVICE_TOKEN={$result['token']}");
        $this->line("DEVICE_FINGERPRINT={$result['fingerprint']}");

        $this->info("\nEt rafraîchis la page !");

        if ($this->confirm('Générer un QR Code à scanner avec le POS pour faciliter l\'installation?', true)) {
            $this->generateQRCode($result, $store);
        }

        return CommandAlias::SUCCESS;
    }

    private function selectStore(): ?Store
    {
        /** @var Collection<Store[]> $stores */
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if (empty($stores)){
            $this->error("Il n'y a aucun store actif.");
            return null;
        }

        if ($stores->count() === 1){
            /** @var Store $store */
            $store = $stores->first();
            $this->info("Store sélectionné: $store->name ($store->sku)");
            return $store;
        }

        $this->table(
            ['#', 'Nom', 'sku', 'Devices'],
            $stores->map(fn(Store $store, int $index) => [
                $index + 1,
                $store->name,
                $store->sku,
                $store->devices()->count()
            ])
        );

        $choice = $this->ask('Sélectionnez le store avec son nombre/sku');
        if (is_numeric($choice)){
            $choiceIndex = ((int) $choice) - 1;
            if(! isset($stores[$choiceIndex])){
                $this->error("Le store avec le numéro $choiceIndex n'a pas été trouvé");
                return null;
            }

            $store = $stores[$choiceIndex];
        } else {
            $choice = (string) $choice;
            $queriedStore = Store::query()
                ->where('is_active', true)
                ->where('sku', $choice)
                ->first();

            if(! $queriedStore){
                $this->error("Le store avec le sku $choice n'a pas été trouvé");
                return null;
            }

            $store = $queriedStore;
        }

        return $store;
    }

    private function selectDeviceType(): ?DeviceType
    {
        $types = DeviceType::cases();

        $this->info("Voici les types de devices disponibles:");
        foreach ($types as $index => $type){
            $this->line(sprintf(
                "%d. %s - %s",
                $index + 1,
                $type->getDisplayName(),
                $type->getDescription()
            ));
        }

        $choice      = $this->ask("Sélectionnez le type de device par son numéro");
        $choiceIndex = ((int) $choice) - 1;

        if(! isset($types[$choiceIndex])){
            $this->error("Le device type avec le numéro $choiceIndex n'a pas été trouvé");
            return null;
        }

        return $types[$choiceIndex];
    }

    private function confirmCreation(Store $store, DeviceType $deviceType, string $name, string $fingerprint): bool
    {
        $this->info("Sommaire de la création:");
        $this->table(
            ['Store', 'Device', 'Nom', 'Fingerprint'],
            [[$store->name, $deviceType->getDisplayName(), $name, $fingerprint]]
        );

        return $this->confirm("Créer le device token ?", true);
    }

    private function generateQRCode(array $result, Store $store): void
    {
        $setupData = [
            'store_slug'          => $store->sku,
            'device_token'        => $result['token'],
            'device_token_name'   => $result['token_name'],
            'device_fingerprint'  => $result['fingerprint'],
            'api_url' => config('app.url') . '/api/v1',
        ];

        $qrData = base64_encode(json_encode($setupData));

        $fileName = 'qrcode:'. $setupData['device_token_name'] . '.png';
        $path     = 'qrcodes/' . $fileName;
        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($qrData);

        Storage::disk('public')->put($path, $qrCode);

        $this->info("\n📱 QR Code Data (base64):");
        $this->line($qrData);
    }
}
