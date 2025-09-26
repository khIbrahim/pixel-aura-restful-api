# Roadmap actuelle (V1 ➜ V1.1) — Synthèse FE/BE avec analyse

Analyse transversale (ce qui est prêt, ce qui manque, interdépendances)
- Auth & Abilities
    - Modèle “device-first, member-second” standardisé. Intersection abilities device ∩ membre gouverne l’UI et l’accès.
    - À verrouiller: config/abilities.php (slugs dot-notation), endpoint /me/abilities exposé. Middleware store_member dépend d’un login membre stable (PIN/code) — OK V1.
- Media & Images
    - Uniformisation: 2 collections (main_image, gallery) et 3 conversions (thumbnail, banner, icon). Routes génériques prêtes.
    - FE: s’attendre à payload “images” standard; utiliser thumbnails pour listing, banner/icon selon besoins.
- Catalog
    - V1 “compact” prêt: ETag/menu_version, structure minimale pour Kiosk/POS.
    - V1.1: pricebooks, schedules, allergènes, recherche (Meilisearch) — hors périmètre immédiat.
- Ordering
    - Statuts et flux alignés Kiosk/POS/KDS. Idempotence obligatoire. Source de vérité des totaux côté serveur.
- Payments (Algérie)
    - Cash V1: drawer/session journaux. Idempotence capture OK. Remboursements/PayPart en V1.1.
- Offline & Sync
    - Queue locale IndexedDB, mapping local_id→server_id, backoff + dédup WS — cadré. FE doit implémenter la file et UI “sync”.
- Events & Temps réel
    - Canaux privés par store/device, enveloppe event standard (event_id, seq, correlation_id). Clients doivent dédupliquer et “pull” après reconnexion.
- Devices Lifecycle
    - Heartbeat, statuses, IP allowlist, fingerprint, token rotation. Admin endpoints BO pour block/unblock/notify. Unique connexion WS recommandée.

Décisions à figer tout de suite (pour éviter la dérive)
- Nommage types REST path param “type”: [categories, items, options, ingredients, option-lists, item-variants].
- x-abilities obligatoires sur tous les endpoints.
- ETag obligatoire sur /v1/catalog/compact; clients doivent envoyer If-None-Match.
- Idempotency-Key obligatoire sur POST /orders et /payments.
- Format argent en cents; dates en ISO 8601 Z.
- WS canaux: private-store.{SKU}.(orders|kds|printing|devices), private-device.{ID}.notifications.

Priorités V1 — Backend (ordre)
1) Finaliser endpoints de base (cf. openapi-v1.yaml):
    - Orders + Payments cash + drawer/session.
    - Catalog compact + media génériques.
    - Members login/logout/me + abilities.
    - Devices heartbeat + broadcasting/auth.
2) Services transverses:
    - PricingService (recalc totaux), IdempotencyStore, AuditService, MediaResource.
3) WebSockets:
    - Émettre tous les events critiques (OrderCreated/Updated, KdsTicket*, PrintJob*).
4) Sécurité:
    - Rate limit per-device; IP allowlist (configurable).
    - Anti-bruteforce login membre (locked_until).
5) Observabilité:
    - Activitylog indexes + purge job; Monolog JSON + correlate.

Priorités V1 — Frontend (ordre)
1) Offline queue (IndexedDB) + UI (file visible, retry/backoff, forcer sync).
2) Catalog compact fetch + ETag (If-None-Match) + affichage.
3) Kiosk:
    - Composer order, imprimer ticket “payer à la caisse” via Gateway, WS orders.
4) POS:
    - Login membre (code+PIN), panier, capture cash idempotente, tickets, drawer/session.
5) KDS:
    - WS kds, actions bump/undo, timers, filtres simples.
6) Gestion media dans BO (upload main + gallery).

Livrables concrets attendus (court terme)
- BE:
    - Implémentation stricte de openapi/openapi-v1.yaml (contrats).
    - Broadcasting/auth avec contrôle abilities par canal.
    - Events WS publiés aux bons moments (transition statuts, impression).
- FE:
    - Client WS (Soketi) avec dédup by event_id, stockage last_seq.
    - Queue offline générique (POST/DELETE/PATCH), mapping local_id→server_id.
    - Intégration Printer Gateway (impression à la création/encaissement).
    - Ecrans: Kiosk (flow simple), POS (encaissement cash + tiroir), KDS (bump).

Risques & garde-fous
- Double capture paiement: mitigé par Idempotency-Key + audit + UI claire.
- Cache catalog obsolète: mitigé par menu_version + ETag 304 + avertir si 422.
- Perte WS: clients doivent “pull” REST après reconnexion; UI indique l’état.

Jalons
- M0 (Backend alpha): endpoints + WS + abilities (2 semaines)
- M1 (Kiosk alpha): offline + création orders + tickets (1–2 semaines)
- M2 (POS alpha): encaissement cash + tiroir/session (2 semaines)
- M3 (KDS alpha): bump/undo + timers (1 semaine)
- M4 (Stabilisation): audits, perfs, UX, doc OpenAPI figée (1–2 semaines)

V1.1 (déjà cadré)
- Sessions membres détaillées (table), exports journaux de caisse, remboursements, PayPart, schedules/pricebooks, recherche, presence WS, delta sync.

Références
- [groups/01-architecture-overview.md](./groups/01-architecture-overview.md)
- [groups/02-catalog.md](./groups/02-catalog.md)
- [groups/03-api-conventions.md](./groups/03-api-conventions.md)
- [groups/04-installation-devices.md](./groups/04-installation-devices.md)
- [groups/05-orders.md](./groups/05-orders.md)
- [groups/06-offline-and-sync.md](./groups/06-offline-and-sync.md)
- [groups/07-abilities.md](./groups/07-abilities.md)
- [groups/08-printing-strategy.md](./groups/08-printing-strategy.md)
- [groups/09-media-images.md](./groups/09-media-images.md)
- [groups/10-operations.md](./groups/10-operations.md)
- [groups/11-store-members.md](./groups/11-store-members.md)
- [groups/12-events-and-realtime.md](./groups/12-events-and-realtime.md)
- [groups/13-payments.md](./groups/13-payments.md)
