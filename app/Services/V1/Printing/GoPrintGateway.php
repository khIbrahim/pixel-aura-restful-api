<?php

namespace App\Services\V1\Printing;

use App\Contracts\V1\Printing\PrintGatewayInterface;
use App\Http\Resources\V1\Order\PrintOrderResource;
use App\Models\V1\Order;
use Illuminate\Support\Facades\Http;

class GoPrintGateway implements PrintGatewayInterface
{

    public function printers(): array
    {
        $response = Http::timeout($this->timeout())
            ->get($this->url($this->printersPath()))
            ->throw();

        return $response->json() ?? [];
    }

    public function print(Order $order, array $printConfig): array
    {
        $order->loadMissing('store', 'items.item', 'creator');

        $payload = new PrintOrderResource($order, $printConfig)
            ->toArray(request());

        $response = Http::timeout($this->timeout())
            ->post($this->url($this->printPath()), $payload)
            ->throw();

        return $response->json() ?? [];
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.print_service.base_url'), '/') . '/' . ltrim($path, '/');
    }

    private function timeout(): int
    {
        return (int) config('services.print_service.timeout', 10);
    }

    private function printPath(): string
    {
        return (string) config('services.print_service.print_path', '/print');
    }

    private function printersPath(): string
    {
        return (string) config('services.print_service.printers_path', '/printers');
    }
}
