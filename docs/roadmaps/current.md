# Roadmap actuelle (V1 ➜ V1.1) — Synthèse FE/BE avec analyse (Mise à jour)

Ce qui est prêt (contrats + design)
- Media & Images
    - 2 collections (main_image, gallery), 3 conversions (thumbnail, banner, icon), endpoints génériques.
- Catalog
    - Payload “compact” ETag/menu_version pour Kiosk/POS; contraintes de base (is_active, stock simple).
- Ordering
    - Statuts unifiés, calculs prix (base + variant.delta + options.delta), idempotence requise.
- Payments (Algérie)
    - Cash V1 (capture/change), sessions & tiroir, journaux. Remboursements/PayPart décalés V1.1.
- Offline & Sync
    - Queue locale IndexedDB, Idempotency-Key, mapping local_id→server_id, backoff + dédup WS.
- Events & Temps réel
    - Canaux privés par store/device, enveloppe event standard (event_id, seq, correlation_id).
- Devices Lifecycle
    - Heartbeat, status, IP allowlist, fingerprint, token rotation; admin minimal BO.

Gros manquants à livrer pour “V1 utilisable”
- Implémentations controllers/services pour:
    - Orders + Payments cash + Drawer/Session
    - Catalog compact + Media génériques
    - Members login/logout/me/pin + /me/abilities
    - Devices heartbeat + broadcasting/auth
    - KDS bump/undo
    - Audit + export
- Clients FE:
    - Offline queue + UI sync
    - Intégration Soketi (dédup et rattrapage REST)
    - Impression via Gateway (customer/kitchen)

Décisions verrouillées
- types REST {categories, items, options, ingredients, option-lists, item-variants}
- x-abilities obligatoires; Idempotency-Key obligatoire sur /orders et /payments
- Argent en cents, ISO 8601 Z; ETag + menu_version sur /catalog/compact
- Canaux WS: private-store.{SKU}.(orders|kds|printing|devices), private-device.{ID}.notifications

Plan de tir FE/BE (résumé opérationnel)
- Backend S1: endpoints + services transverses + WS auth + ETag/Idempotence
- Frontend S1: offline queue + Kiosk flow + WS client
- Backend S2: payments/drawer/session + KDS + audit/export
- Frontend S2: POS encaissement + KDS board + BO médias
- Stabilisation: tests E2E, perfs, logs, docs, staging

Risques & garde-fous
- Paiements doublons → Idempotency-Key + audit + UI
- Catalog obsolète → ETag 304 + menu_version + message 422 “menu changed”
- WS down → clients “pull” REST; indicateur online/offline

Jalons (inchangés)
- M0 (BE alpha): endpoints + WS + abilities (2 sem.)
- M1 (Kiosk alpha): offline + orders + tickets (1–2 sem.)
- M2 (POS alpha): cash + drawer/session (2 sem.)
- M3 (KDS alpha): bump/undo + timers (1 sem.)
- M4 (Stabilisation): audits/perfs/UX/docs (1–2 sem.)

V1.1 (déjà cadré, hors V1)
- Sessions membres détaillées, exports journaux, remboursements, PayPart, pricebooks/schedules, recherche, presence WS, delta sync.

Pour le moral: on n’a pas “fini” tant que le code des routes n’est pas là. Ce message fige le périmètre V1; on exécute. Je reste focus et je drive les prochains commits.
ma vision mtn:
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\MeController;
use App\Http\Controllers\V1\MediaController;
use App\Http\Controllers\V1\CatalogCompactController;
use App\Http\Controllers\V1\OrdersController;
use App\Http\Controllers\V1\OrderStatusController;
use App\Http\Controllers\V1\PaymentsController;
use App\Http\Controllers\V1\SessionsController;
use App\Http\Controllers\V1\DrawerController;
use App\Http\Controllers\V1\KdsTicketsController;
use App\Http\Controllers\V1\MembersController;
use App\Http\Controllers\V1\AbilitiesController;
use App\Http\Controllers\V1\DevicesController;
use App\Http\Controllers\V1\AuditController;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function () {
        Route::get('/catalog/compact', [CatalogCompactController::class, 'index']);

        Route::post('/orders', [OrdersController::class, 'store']);
        Route::get('/orders/{id}', [OrdersController::class, 'show']);
        Route::patch('/orders/{id}/status', [OrderStatusController::class, 'update']);

        Route::post('/orders/{id}/payments', [PaymentsController::class, 'store']);
        Route::post('/sessions/open', [SessionsController::class, 'open']);
        Route::post('/sessions/close', [SessionsController::class, 'close']);
        Route::post('/drawer/open', [DrawerController::class, 'open']);
        Route::post('/drawer/payout', [DrawerController::class, 'payout']);
        Route::post('/drawer/payin', [DrawerController::class, 'payin']);

        // KDS
        Route::post('/kds/tickets/{id}/bump', [KdsTicketsController::class, 'bump']);
        Route::post('/kds/tickets/{id}/undo', [KdsTicketsController::class, 'undo']);

        // Devices
        Route::post('/devices/{id}/heartbeat', [DevicesController::class, 'heartbeat']);

        // Audit
        Route::get('/audit', [AuditController::class, 'index']);
        Route::get('/audit/export', [AuditController::class, 'export']);
    });

Route::post('/broadcasting/auth', BroadcastingAuthController::class);
```
