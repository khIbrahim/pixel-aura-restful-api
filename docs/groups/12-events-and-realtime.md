# Événements & Temps réel (V1)

Objectif
- Standardiser les événements temps réel pour gérer Devices, Orders, KDS et Printing.
- Définir les canaux, l’auth, l’enveloppe d’événement, les schémas de payload, et les règles de résilience.
- Indiquer qui s’abonne à quoi (POS, Kiosk, KDS, PrinterGateway, Back Office) et comment dédupliquer/synchroniser.

Transport & hébergeur
- Pusher protocol via Soketi (self-hosted).
- Diffusion Laravel Broadcasting (events PHP), drivers: pusher + soketi.
- Politique V1: au-moins-une-fois (at-least-once). Les clients doivent dédupliquer (event_id/seq).

Canaux (nomenclature)
- Par store (privés):
    - private-store.{STORE_SKU}.orders
    - private-store.{STORE_SKU}.kds
    - private-store.{STORE_SKU}.printing
    - private-store.{STORE_SKU}.devices (administration/monitoring)
- Par device (privés):
    - private-device.{DEVICE_ID}.notifications
- Présence (optionnel BO/Kitchen):
    - presence-store.{STORE_SKU}.kds.presence (V1.1)
      Notes
- Préfixes “private-”/“presence-” obligatoires côté Pusher pour auth.
- STORE_SKU est l’identifiant public standard (voir provisioning V1).

Abonnement par type de client
- POS
    - private-store.{SKU}.orders
    - private-store.{SKU}.printing
    - private-device.{DEVICE_ID}.notifications
- Kiosk
    - private-store.{SKU}.orders
    - private-device.{DEVICE_ID}.notifications
- KDS
    - private-store.{SKU}.kds
    - private-device.{DEVICE_ID}.notifications
- PrinterGateway
    - private-store.{SKU}.printing
    - private-device.{DEVICE_ID}.notifications
- Back Office/Monitoring
    - private-store.{SKU}.devices
    - private-store.{SKU}.orders / .kds (lecture)

Authentification WS (Pusher)
- Endpoint Laravel: POST /broadcasting/auth
    - Entrées: channel_name, socket_id
    - Vérifications:
        - Token Sanctum du device valide, non bloqué, IP allowlist (si activée)
        - Abilities minimales selon canal:
            - orders: order.read
            - kds: kds.read
            - printing: printer.read ou ticket.create (selon client)
            - devices: devices.read (BO)
            - device.{id}.notifications: device.sync (et device_id == contexte)
- Throttling:
    - 1 connexion WS active par device (reconnexion backoff exponentiel)
    - Message rate: 30 msg/10s par device (recommandation Soketi)

Enveloppe d’événement (standard)
```json
{
  "v": 1,
  "event": "OrderCreated",
  "event_id": "079ab8a5-7a9e-4c62-9f0e-7dc2b8a6e2a1",
  "seq": 12873,
  "occurred_at": "2025-09-26T13:59:10.321Z",
  "store": { "id": 7, "sku": "pizza-bab-el-oued" },
  "sender": { "device_id": 42, "device_type": "Kiosk" },
  "subject": { "type": "Order", "id": 123456 },
  "correlation_id": "6c3b1a6a-9b5d-4f25-9e71-777f6d0f6db2",
  "data": { "...payload spécifique..." },
  "meta": { "replay": false }
}
```
- v: version d’enveloppe (1).
- event_id: UUID pour déduplication.
- seq: séquence monotone par canal et par store (base serveur). Utile pour “rejouer depuis”.
- correlation_id: injecté par middleware “correlate” si disponible.
- meta.replay: true si l’événement est renvoyé après reconnexion (V1.1).

Règles de résilience (clients)
- Déduplication: conserver le dernier event_id par sujet et le dernier seq par canal; ignorer doublons.
- Reconnexion:
    - V1: refaire un GET de rattrapage (ex: /v1/orders/{id} ou listes with updated_since) après reconnexion.
    - V1.1: endpoint /v1/events/since?channel=...&seq=... pour delta-replay.
- Offline:
    - Suspendre WS, conserver last_seq; à la reprise: “push queue” (mutations) puis “pull” (GET compact + listes).

Catalogue d’événements (V1)
- Orders
    - OrderCreated, OrderUpdated, OrderStatusChanged
- KDS
    - KdsTicketCreated, KdsTicketUpdated
- Printing
    - PrintJobQueued, PrintJobCompleted, PrintJobFailed
- Devices
    - DeviceStatusChanged (online/offline/inactive/blocked)
    - DeviceNotification (texte/action ciblée)
- Media (optionnel)
    - MediaUpdated (pour invalider caches si nécessaire)

Schémas — payloads “data”
- OrderCreated
```json
{
  "order": {
    "id": 123456,
    "channel": "kiosk",
    "status": "accepted",
    "totals_cents": { "subtotal": 28000, "tax": 0, "total": 28000 },
    "number": "A047",
    "created_at": "2025-09-26T13:59:10Z"
  }
}
```
- OrderStatusChanged
```json
{ "order_id": 123456, "from": "in_preparation", "to": "ready", "at": "2025-09-26T14:04:22Z" }
```
- KdsTicketCreated
```json
{
  "ticket": {
    "id": 98765,
    "order_id": 123456,
    "items": [
      { "id": 1, "name": "Classic Burger", "qty": 2, "modifiers": ["Cheese","No onion"] }
    ],
    "status": "in_preparation",
    "notes": "",
    "created_at": "2025-09-26T14:00:02Z"
  }
}
```
- KdsTicketUpdated
```json
{ "ticket_id": 98765, "status": "ready", "at": "2025-09-26T14:05:44Z" }
```
- PrintJobQueued
```json
{
  "print_job": {
    "id": 5551,
    "type": "customer_receipt",
    "target_printer_id": 31,
    "order_id": 123456
  }
}
```
- DeviceStatusChanged
```json
{
  "device": { "id": 42, "type": "PrinterGateway", "status": "online" },
  "reason": "heartbeat",
  "at": "2025-09-26T14:06:01Z"
}
```
- DeviceNotification
```json
{
  "title": "Mise à jour disponible",
  "message": "Une nouvelle configuration d’imprimante a été appliquée.",
  "level": "info",
  "action": { "type": "navigate", "target": "settings/printers" }
}
```

KDS — règles spécifiques (dans ce document)
- Statuts V1: in_preparation → ready → served (ou delivered). Undo autorisé (ready → in_preparation).
- Actions KDS (REST) déclenchent events:
    - POST /v1/kds/tickets/{id}/bump { to: "ready" } → KdsTicketUpdated
    - POST /v1/kds/tickets/{id}/undo { to: "in_preparation" } → KdsTicketUpdated
- Timers:
    - Client KDS calcule le “temps en production” localement (occurred_at de KdsTicketCreated).
- Filtres:
    - V1: par catégorie (ex: “Pizza”, “Burger”) selon mapping sur Item/Category (prep_station V1.1).
    - Les clients peuvent filtrer côté UI; le serveur peut inclure category_ids dans KdsTicketCreated.

Sécurité & permissions (rappel)
- Abilities requises pour recevoir/émettre:
    - orders.* → lecture WS; order.status.set pour actions
    - kds.read / kds.update
    - printer.read / ticket.create
    - devices.read (canal devices)
- Auth Pusher: via Sanctum + vérification abilities dans /broadcasting/auth.

Diagnostics & monitoring
- Métriques Soketi: /metrics (port 9601) — connexions, throughput.
- Logs d’événements:
    - Activitylog (audit) pour actions critiques (ex: KDS bump, impression échouée).
    - Monolog JSON avec correlation_id et channel.
- Health WS:
    - Ping/pong client-side; relance sur timeout (>30s sans message).
    - Device heartbeat reste en REST (POST /devices/{id}/heartbeat) mais peut déclencher DeviceStatusChanged.

Bonnes pratiques client
- Stocker last_seq par canal (ex: localStorage/indexedDB).
- Dédupliquer par event_id + sujet.
- Après reconnexion: GET compact ou listes with updated_since, puis reprendre l’écoute WS.
- Ne pas dépendre du WS pour la source de vérité; toujours pouvoir rafraîchir REST.

Évolution (V1.1+)
- Replay serveur: /v1/events/since (par canal) avec seq.
- Presence channels KDS (voir la liste des écrans connectés).
- Segmentation KDS par stations (prep_station) et canaux dédiés: private-store.{SKU}.kds.station.{slug}.
- QoS: priorités d’événements (impression vs UI).
- Bridge AMQP (RabbitMQ) pour intégrations tierces (analytics, ETL).

Notes (prises des autres docs)
- Canaux standardisés utilisés partout: store.{SKU}.orders/kds/printing, device.{ID}.notifications.
- Enveloppe inclut correlation_id (middleware correlate), utile pour l’audit.
- Offline & Sync: lastReceivedAt/last_seq pour rattrapage; WS au-moins-une-fois.
