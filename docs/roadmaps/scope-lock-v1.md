# V1 Scope Lock — Plan d’exécution FE/BE

Objectif
- Geler le périmètre V1 pour livrer une version fonctionnelle et testable rapidement.
- Lister précisément les endpoints, écrans, événements, et critères d’acceptation.
- Donner un plan d’action court (2–4 semaines) pour FE/BE.

1) Périmètre V1 (do now)
- Catalog
    - GET /v1/catalog/compact?channel&lang (ETag/menu_version).
    - Media génériques main/gallery pour categories/items (upload/replace/delete).
- Orders
    - POST /v1/orders (Idempotency-Key), GET /v1/orders/{id}, PATCH /v1/orders/{id}/status.
- Payments (cash seulement)
    - POST /v1/orders/{id}/payments (Idempotency-Key).
    - Sessions & drawer: /v1/sessions/open|close, /v1/drawer/open|payout|payin.
- KDS
    - POST /v1/kds/tickets/{id}/bump, /v1/kds/tickets/{id}/undo.
- Devices
    - POST /v1/devices/{id}/heartbeat, /broadcasting/auth (Pusher/Soketi).
- Audit
    - GET /v1/audit, GET /v1/audit/export (CSV/JSON). *_(service déjà fait)_*

2) Hors périmètre (V1.1+)
- Remboursements, PayPart, PriceBooks, Schedules de disponibilité, Recettes (BOM), Inventaire avancé, Presence WS, Delta sync.

3) Écrans FE (livrables)
- Kiosk
    - Menu (compact, ETag), Panier, Validation commande, Ticket “payer à la caisse”, État commande via WS.
- POS
    - Login membre (code+PIN), Panier, Capture cash (idempotent), Tickets, Drawer/Session, Liste commandes.
- KDS
    - Board tickets (WS), bump/undo, timers, filtres simples.
- BO (minimal)
    - Upload médias (main/gallery) pour catégories/items.
    - Liste audit (lecture + export).

4) Critères d’acceptation (exemples)
- Idempotence
    - Double POST /orders et /payments avec même Idempotency-Key renvoie la même ressource (201/200).
- Offline
    - 10 commandes créées offline → sync sans doublons, mapping local_id→server_id correct.
- Catalog cache
    - If-None-Match → 304 si inchangé; refresh automatique si 200 (menu_version change).
- WS
    - À chaque transition majeure (order status, KDS bump) → event reçu par client abonné.
- Sécurité
    - Abilities: endpoint refuse 403 si ability manquante.
    - Device IP hors allowlist → 423.
    - Membre PIN bruteforce → locked_until.

5) Plan d’exécution (2–4 semaines)
- Semaine 1 (BE)
    - Branch “v1-scope-lock”:
        - PricingService, IdempotencyStore, AuditService.
        - Media endpoints génériques, Catalog compact + ETag.
        - /broadcasting/auth avec contrôle x-abilities par canal.
- Semaine 1 (FE)
    - Client WS (dédup event_id, last_seq).
    - Offline queue (IndexedDB), UI sync, mapping local_id.
    - Kiosk: menu + panier + create order + ticket “payer à la caisse”.
- Semaine 2 (BE)
    - Payments cash + drawer/session + events PrintJob*.
    - KDS bump/undo + events KdsTicket*.
    - Audit + export CSV.
- Semaine 2 (FE)
    - POS: login code+PIN, encaissement cash, tickets, drawer/session.
    - KDS: board + bump/undo + timers.
- Stabilisation (1–2 semaines)
    - Tests E2E (idempotence, offline, WS), perfs (compacts < 1–2 MB gz), logs JSON, purge activity.

6) Définition de terminé (DoD V1)
- OpenAPI par groupe validé par FE/BE.
- CI: tests unitaires pricing/idempotence; lints.
- Environnements: staging Docker-compose (Soketi, Redis, MySQL), domaine TLS.
- Démo: créer/encaisser/imprimer sur machines de test (POS+Kiosk+Gateway+KDS).
- Docs: Quickstart FE/BE, Ops checklist, Roadmap V1.1 figée.
