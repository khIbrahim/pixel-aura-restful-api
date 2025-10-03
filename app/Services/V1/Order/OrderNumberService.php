<?php

namespace App\Services\V1\Order;

use App\Contracts\V1\Order\OrderRepositoryInterface;
use App\Models\V1\Order;
use Illuminate\Support\Facades\DB;

readonly class OrderNumberService
{

    public function __construct(
        private OrderRepositoryInterface $repository
    ){}

    public function generate(int $storeId): string
    {
        return DB::transaction(function () use ($storeId){
            $today = today()->format('ymd');

            /** @var Order $lastOrder */
            $lastOrder = $this->repository->query()
                ->where('store_id', $storeId)
                ->whereDate('created_at', today())
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $sequence = $lastOrder ? $this->extractSequence($lastOrder->number) + 1 : 1;

            return 'ORD-' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        });
    }

    private function extractSequence(string $number): int
    {
        $parts = explode('-', $number);
        return (int) end($parts);
    }

    public function isValidFormat(string $number): bool
    {
        return preg_match("/^ORD-\d{6}-\d{3}$/", $number);
    }

}
