<?php

use App\Http\Controllers\V1\MeController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function () {
        Route::get('/me', [MeController::class, 'me']);
    });

Route::post('v1/print', function() {
$payload = [
    "order_number" => "CMD-2511070116",
    "order_type" => "delivery",
    "created_at" => "2025-11-07T01:16:35Z",
    "store" => [
        "name" => "Le Gourmet",
        "address" => "15 Rue des Oliviers, Kolea",
        "phone" => "0553190042",
    ],
    "delivery" => [
        "address" => "Cite les 4 Chemins, Bat C, Appt 12",
        "contact_name" => "Khaled Ibrahim",
        "contact_phone" => "0553190042",
        "notes" => "Sonner 2 fois",
    ],
    "items" => [
        [
            "name" => "Pizza Margherita",
            "quantity" => 1,
            "unit_price" => "850 DA",
            "total_price" => "850 DA",
            "notes" => "",
            "options" => [
                [
                    "name" => "Taille: Grande",
                    "quantity" => 1,
                    "price" => "150 DA",
                    "action" => "add",
                ],
            ],
            "ingredients" => [
                [
                    "name" => "Olives",
                    "action" => "remove",
                    "quantity" => 0,
                    "price" => "0 DA",
                ],
                [
                    "name" => "Extra fromage",
                    "action" => "add",
                    "quantity" => 1,
                    "price" => "100 DA",
                ],
            ],
        ],
        [
            "name" => "Tacos Poulet",
            "quantity" => 2,
            "unit_price" => "450 DA",
            "total_price" => "900 DA",
            "notes" => "",
            "options" => [
                [
                    "name" => "Sauce: Algerienne",
                    "quantity" => 1,
                    "price" => "0 DA",
                    "action" => "add",
                ],
            ],
            "ingredients" => [],
        ],
        [
            "name" => "Coca-Cola 1L",
            "quantity" => 2,
            "unit_price" => "150 DA",
            "total_price" => "300 DA",
            "notes" => "",
            "options" => [],
            "ingredients" => [],
        ],
    ],
    "pricing" => [
        "subtotal" => "2 200 DA",
        "tax" => "0 DA",
        "discount" => "0 DA",
        "delivery_fee" => "200 DA",
        "total" => "2 400 DA",
    ],
    "notes" => "",
    "print" => [
        "customer" => "XPRINTER D-200N",
        "receipt" => "XPRINTER D-200N",
        "kitchen" => "XPRINTER D-200N",
    ],
    "payement_method" => "Cash",
    "creator" => "Khaled Ibrahim",
];

//    $order = Order::query()
//        ->findOrFail(39)
//        ->load(['store', 'items', 'creator']);
//
//    $payload = new PrintOrderResource($order, [
//        'receipt'  => 'XPRINTER D-200N',
//    ])->toArray(request());

    $resp = Http::timeout(10)
        ->post('http://host.docker.internal:8080/print', $payload);

    return response()->json($resp->json());
});

Route::get('v1/printers', function() {
    $printers = [
            'XPRINTER D-200N' => [
                'name' => 'XPRINTER D-200N',
                'ip' => '192.168.100.50',
                'port' => 9100,
                'is_active' => true,
                'timeout' => 15,
                'max_width' => 80,
            ],
            'EPSON TM-T20III' => [
                'name' => 'EPSON TM-T20III',
                'ip' => '192.168.100.51',
                'port' => 9100,
                'is_active' => true,
                'timeout' => 5,
                'max_width' => 48,
            ],
            'CITIZEN CT-S310' => [
                'name' => 'CITIZEN CT-S310',
                'ip' => '192.168.100.53',
                'port' => 9100,
                'is_active' => true,
                'timeout' => 7,
                'max_width' => 58,
            ],
            'BIXOLON SRP-350III' => [
                'name' => 'BIXOLON SRP-350III',
                'ip' => '192.168.100.54',
                'port' => 9100,
                'is_active' => true,
                'timeout' => 6,
                'max_width' => 80,
            ],
    ];

    return response()->json(['data' => $printers]);
});
