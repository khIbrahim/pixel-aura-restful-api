# Orders — Spécification Fonctionnelle & Technique (V1)

Objectif
- Définir clairement la création et le cycle de vie des commandes pour POS/Kiosk/KDS.
- Cadrer les calculs de prix, l’idempotence, l’offline/sync, les événements WS, l’impression.
- Servir de contrat commun FE/BE (complément à openapi/orders.yaml et payments.yaml).

Terminologie
- Order: commande.
- OrderItem: ligne de commande (item, variant, options).
- Channel: pos | kiosk | web.
- KDS: Kitchen Display System (écran cuisine).
- Ticket client/cuisine: imprimés via Printer Gateway.

Modèle de données (conceptuel, V1)
- Order
    - id, store_id, channel, status: created|accepted|in_preparation|ready|served|picked_up|cancelled|refunded
    - totals_cents: { subtotal, tax, total } (int)
    - created_at, updated_at
    - number? (affichage public, optionnel V1)
- OrderItem
    - id, order_id, item_id, item_name, qty
    - unit_price_cents (base+variant delta), total_cents (unitaire+options)×qty
    - variant_id?, variant_name?
    - options: [{ option_id, name, price_delta_cents }]
    - notes?

Règles de calcul (V1)
- unit_price = base_price_cents + variant.price_delta_cents (si présent)
- option_total = somme(price_delta_cents des options)
- line_total = (unit_price + option_total) × qty
- subtotal = somme(line_total)
- taxes:
    - store.tax_inclusive = true → total = subtotal (TTC, tax dérivée)
    - store.tax_inclusive = false → total = subtotal + tax_cents (V1 simple, rate par item/store)
- Tous les montants sont en cents (int). DZD cash: pas de décimales.

Machine à états (V1)
- created → accepted → in_preparation → ready → served|picked_up
- accepted → cancelled (avant préparation)
- ready → cancelled (rare, décision BO)
- refunded (post-paiement, V1.1)
- Kiosk: created (client) → accepted (serveur) automatique si OK.
- POS: peut aller jusqu’à served/picked_up.
- KDS: gère in_preparation ↔ ready (undo autorisé: ready → in_preparation).

Validations (à la création)
- items non vides; qty ≥ 1; item/variant/options valides et actifs
- prix recalculés côté serveur (source de vérité)
- stock (si track_inventory): V1 simple — pas de décrément automatique, à cadrer V1.1
- si incohérence (item désactivé, option invalide) → 422

Idempotence
- POST /v1/orders et POST /v1/orders/{id}/payments exigent le header Idempotency-Key (UUID).
- Comportement:
    - clé déjà vue (même payload logique) → 200/201 avec la même ressource
    - clé en conflit → 409 + ressource existante (client doit dédupliquer)
- Stockage: IdempotencyStore persiste (key, scope, response hash, resource_id, ttl).

Offline & mapping local_id
- Client génère local_id (UUID) pour chaque commande offline.
- Serveur écho le local_id et renvoie order_id.
- Paiement queue en dépendance: doit attendre que la création soit synchronisée (mapping local→server résolu).
- Voir docs/06-offline-and-sync.md pour la structure IndexedDB et la politique de retry/backoff.

Événements WS (résumé)
- OrderCreated, OrderUpdated, OrderStatusChanged
- Enveloppe standard: v, event, event_id, seq, occurred_at, store, sender, subject, correlation_id, data (cf. docs/12-events-and-realtime.md)
- Clients: dédupliquent via event_id/seq, et “pull” REST après reconnexion.

Impression (Printer Gateway)
- À la capture cash (POS): ticket client.
- À la création acceptée (si cuisine): ticket cuisine (peut être émis dès accepted ou in_preparation).
- Kiosk “payer à la caisse”: ticket client “à payer” (pas de prix si voulu, à définir via template).

Endpoints (contrat — voir OpenAPI)
- POST /v1/orders (order.create)
    - Body: channel, items[{item_id, qty, variant_id?, options[] }], notes?, local_id?
    - x-abilities: ['order.create']
- GET /v1/orders/{id} (order.read)
    - x-abilities: ['order.read']
- PATCH /v1/orders/{id}/status (order.status.set)
    - Body: { to: 'accepted'|'in_preparation'|'ready'|'served'|'picked_up'|'cancelled'|'refunded' }
    - x-abilities: ['order.status.set']
- POST /v1/orders/{id}/payments (payment.capture — cash)
    - Body: { method: 'cash', amount_cents }
    - Headers: Idempotency-Key obligatoire
    - x-abilities: ['payment.capture']

Flux Kiosk (V1)
1) Fetch catalog compact (ETag/menu_version) → composer panier
2) POST /orders (Idempotency-Key) → réponse 201 { data: Order, local_id }
3) Imprimer ticket “payer à la caisse” (si configuré)
4) WS: suivre les transitions (accepted, in_preparation, ready)

Flux POS (V1)
1) Panier → POST /orders → 201
2) POST /orders/{id}/payments { cash } (Idempotency-Key)
3) Imprimer ticket client; ouvrir tiroir (drawer.open)
4) Suivre KDS (WS) et clôturer (served/picked_up)

Flux KDS (V1)
- WS: KdsTicketCreated (lié à Order)
- Actions:
    - bump: POST /v1/kds/tickets/{id}/bump { to: 'ready' }
    - undo: POST /v1/kds/tickets/{id}/undo { to: 'in_preparation' }
- Émet OrderStatusChanged si couplé (ex: dernière ligne ready → order ready)

Sécurité & abilities
- order.create, order.read, order.status.set
- payment.capture (POS); ticket.create, drawer.open (impression/tiroir)
- Intersection abilities device ∩ membre (voir docs/06-abilities-and-auth.md et docs/08-store-members.md)

Codes d’erreur usuels
- 400: payload invalide (format)
- 401: token device invalide/expiré
- 403: ability manquante
- 409: idempotency conflict ou transition de statut invalide
- 422: validations métier (item inactif, qty <= 0, montant insuffisant)
- 423: device bloqué / IP refusée

Exemples

- Création d’une commande (request)
```json
{
  "channel": "kiosk",
  "local_id": "ord-local-8b8b-2c…",
  "items": [
    { "item_id": 345, "qty": 2, "variant_id": 2, "options": [92] }
  ],
  "notes": "Sans oignon"
}
```

- Réponse (201)
```json
{
  "data": {
    "id": 123456,
    "channel": "kiosk",
    "status": "accepted",
    "totals_cents": { "subtotal": 28000, "tax": 0, "total": 28000 },
    "items": [
      {
        "id": 1,
        "item_id": 345,
        "item_name": "Classic Burger",
        "qty": 2,
        "unit_price_cents": 13000,
        "variant_id": 2,
        "variant_name": "Double",
        "options": [{ "option_id": 92, "name": "Bacon", "price_delta_cents": 800 }],
        "total_cents": 28000
      }
    ],
    "created_at": "2025-09-26T14:20:00Z"
  },
  "local_id": "ord-local-8b8b-2c…"
}
```

- Changement de statut
```json
{ "to": "in_preparation" }
```

- Capture cash (request)
```json
{
  "method": "cash",
  "amount_cents": 30000
}
```

- Réponse (201 Payment)
```json
{
  "data": {
    "id": 98701,
    "order_id": 123456,
    "method": "cash",
    "amount_cents": 30000,
    "change_cents": 2000,
    "status": "captured",
    "captured_at": "2025-09-26T14:22:30Z"
  }
}
```

Tests (checklist FE/BE)
- Idempotence:
    - Double POST /orders (même Idempotency-Key) → 201/200 identique
    - Double POST /payments (même clé) → 201/200 identique
- Validations:
    - item inactif → 422
    - qty=0 → 422
- Transitions:
    - accepted → in_preparation → ready → served (OK)
    - ready → in_preparation (undo) via KDS (OK)
    - served → ready (KO 409)
- Offline:
    - 10 commandes offline, reconnexion → mapping local→server OK; pas de doublons
- WS:
    - À la création: OrderCreated reçu
    - Changement de statut: OrderStatusChanged reçu
    - Déduplication event_id → pas de duplication en UI

Références
- OpenAPI:
    - openapi/orders.yaml
    - openapi/payments.yaml
- Docs:
    - docs/04-domain-ordering.md (vision globale)
    - docs/06-offline-and-sync.md (offline queue)
    - docs/12-events-and-realtime.md (WS)
    - docs/11-audit-and-logging.md (audit)
    - docs/14-payments-algeria.md (paiement cash, tiroir, sessions)
