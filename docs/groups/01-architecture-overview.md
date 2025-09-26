# Architecture Overview (V1)

Objectif
- Donner une vue claire, actionnable et cohérente pour FE/BE sur l’ensemble du système V1.
- Résumer comment Catalog, Orders, Payments (cash), Media, Members, Devices, Events/WS, Offline s’imbriquent.
- Servir de carte mentale + conventions transverses (IDs, argent, dates, auth, abilities, ETag, idempotence).

Vue d’ensemble (composants)
- API Laravel (HTTP + Broadcasting):
    - REST /v1 (JSON), Sanctum (Device-first), middleware: auth:sanctum, device.ctx, device.throttle:per-device, correlate, store_member.
    - Broadcast WS via Soketi (Pusher protocol).
- Soketi (WebSockets):
    - Canaux privés par store: orders, kds, printing, devices.
    - Canaux privés par device: notifications.
- Redis:
    - Cache, queues, websockets auth rate limit, sessions futures (V1.1).
- MySQL:
    - Données métier: Catalog, Orders, Payments, Members, Media (Spatie), Activitylog.
- Storage S3 (ou Minio):
    - Medias: original + conversions (thumbnail 300x300, banner 1200x600, icon 64x64).
- Printer Gateway (site restaurant):
    - Reçoit PrintJob via WS, imprime tickets client/cuisine, ouvre tiroir (RJ11).

Flux clés
- Kiosk:
    - Lit catalog “compact” (ETag/menu_version) → compose une commande → crée l’order (offline possible) → impression ticket client (“payer à la caisse” V1).
- POS:
    - Lit catalog, gère la session de caisse, capture paiement cash (idempotent), imprime ticket client, suit KDS.
- KDS:
    - Reçoit KdsTicketCreated/Updated en WS, bump/undo statuts, timers côté client.
- Media:
    - Tous modèles ont deux collections: main_image, gallery. Conversions synchrones (nonQueued): thumbnail, banner, icon.
- Members:
    - Device-first puis login membre (code + PIN). Abilities effectives = intersection device ∩ membre.

Conventions transverses
- Identifiants:
    - Public store: STORE_SKU (dans headers X-Store-Sku).
    - IDs numériques pour entités; UUID pour idempotency, local_ids offline.
- Argent:
    - Toujours en cents (int). DZD par défaut sans décimal en cash; garder cents pour cohérence interne.
- Dates:
    - ISO 8601 (UTC, suffixe Z). Include created_at/updated_at si utile aux clients.
- Caching:
    - ETag/If-None-Match sur catalog compact; menu_version pour invalider.
- Idempotence:
    - Header Idempotency-Key sur mutations critiques (orders, payments).
- x-abilities:
    - Chaque endpoint déclare les abilities requises (dot-notation, cf. config/abilities.php).
- Sécurité:
    - Rate limit per-device, IP allowlist (devices), anti-bruteforce login membre, audit Spatie.

Domaines et dépendances
- Catalog -> Orders (validation item/variant/options, prix).
- Orders -> Payments (capture cash) -> Printing (ticket client/cuisine) -> KDS (préparation).
- Members/Abilities contrôlent ce que POS/Kiosk/KDS peuvent faire.
- Events/WS propagent les changements aux UIs (au-moins-une-fois → déduplication côté client).
- Offline & Sync gère la résilience (queue locale, mapping local→serveur, retry/backoff).
