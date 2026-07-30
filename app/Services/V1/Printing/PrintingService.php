<?php

namespace App\Services\V1\Printing;

use App\Contracts\V1\Printing\PrintGatewayInterface;
use App\Contracts\V1\Printing\PrintingServiceInterface;
use App\Contracts\V1\Printing\StorePrintSettingsRepositoryInterface;
use App\Models\V1\Order;
use App\Models\V1\StorePrintSettings;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

readonly class PrintingService implements PrintingServiceInterface
{

    public function __construct(
        private StorePrintSettingsRepositoryInterface $repository,
        private PrintGatewayInterface                 $gateway
    ){}

    public function printers(): array
    {
        return $this->gateway->printers();
    }

    public function settings(int $storeId): StorePrintSettings
    {
        return $this->repository->settingsForStore($storeId);
    }

    public function updateSettings(int $storeId, array $payload): StorePrintSettings
    {
        return $this->repository->updateForStore($storeId, $payload);
    }

    public function printOrder(Order $order, array $types): array
    {
        $settings    = $this->settings($order->store_id);
        $printConfig = $this->buildPrintConfig($settings, $types);

        if(empty($printConfig)){
            throw new RuntimeException("Aucune imprimante n'est configurée pour ce ticket.");
        }

        return $this->gateway->print($order, $printConfig);
    }

    public function printOrderCreatedTickets(Order $order): void
    {
        $settings = $this->settings($order->store_id);
        $types    = [];

        if($settings->auto_print_customer_on_order_created){
            $types[] = 'customer';
        }

        if($settings->auto_print_receipt_on_order_created){
            $types[] = 'receipt';
        }

        $this->printSafely($order, $types, 'order_created');
    }

    public function printKitchenTicket(Order $order): void
    {
        $settings = $this->settings((int) $order->store_id);

        if(! $settings->auto_print_kitchen_on_preparing){
            return;
        }

        $this->printSafely($order, ['kitchen'], 'order_preparing');
    }

    private function printSafely(Order $order, array $types, string $trigger): void
    {
        if(empty($types)){
            return;
        }

        try {
            $response = $this->printOrder($order, $types);

            Log::info("Impression automatique de la commande envoyée.", [
                'order_id' => $order->id,
                'store_id' => $order->store_id,
                'types'    => $types,
                'trigger'  => $trigger,
                'response' => $response,
            ]);
        } catch(Throwable $e){
            Log::warning("L'impression automatique de la commande a échoué.", [
                'order_id'  => $order->id,
                'store_id'  => $order->store_id,
                'types'     => $types,
                'trigger'   => $trigger,
                'message'   => $e->getMessage(),
                'exception' => $e::class,
            ]);
        }
    }

    private function buildPrintConfig(StorePrintSettings $settings, array $types): array
    {
        $config = [];

        foreach(array_unique($types) as $type){
            $printer = match($type){
                'customer' => $settings->customer_printer_name ?? config('services.print_service.printers.customer') ?? config('services.print_service.default_printer'),
                'receipt'  => $settings->receipt_printer_name ?? config('services.print_service.printers.receipt') ?? config('services.print_service.default_printer'),
                'kitchen'  => $settings->kitchen_printer_name ?? config('services.print_service.printers.kitchen') ?? config('services.print_service.default_printer'),
                default    => null,
            };

            if($printer){
                $config[$type] = $printer;
            }
        }

        return $config;
    }
}
