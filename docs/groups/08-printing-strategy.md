# Stratégie d’impression (V1)

Objectif
- Comparer deux approches d’impression: Printer Gateway locale vs Impression Cloud.
- Recommander une approche V1, l’architecture, les endpoints/événements, la sécurité, et la liste matériel.

TL;DR (recommandation V1)
- Choix: Printer Gateway locale (fiable, faible latence, offline-friendly).
- File d’attente: Redis (V1), option RabbitMQ en V1.1 si charge/interop.
- Temps réel: Soketi (self-hosted) compatible Pusher.
- Imprimantes: ESC/POS réseau (LAN) ou USB via Gateway.
- Tickets: templates serveur *_(on va gérer ça avec le frontend tt façon)_* -> ESC/POS (ou Markdown->ESC/POS) côté Gateway.

1) Approche A — Printer Gateway locale
- Description: Un petit service local (sur la caisse POS ou un mini PC) reçoit des “PrintJobs” depuis l’API via WebSockets et/ou REST, puis envoie sur l’imprimante (LAN/USB/Série).
- Avantages
    - Très faible latence, fiable en LAN, support USB (ou RJ11 pour tiroir via imprimante).
    - Résilience offline: la Gateway peut bufferiser les jobs si l’API est temporairement indisponible.
    - Contrôle d’ouverture tiroir-caisse.
- Inconvénients
    - Nécessite déployer un agent local (installation initiale).
- Use cases V1
    - Ticket client, ticket cuisine (KDS complémentaire), ouverture tiroir, duplicata.
- Protocoles support
    - ESC/POS RAW (TCP 9100 pour imprimantes LAN), USB CDC/Raw, Série.
    - Imprimantes cibles: Epson TM-T20III (LAN), XPrinter 80 LAN, Sunmi intégrée (via SDK, V1.1).
- Sécurité
    - Auth par DEVICE_TOKEN (PrinterGateway), abilities: 'printer.read','printer.update','ticket.create','device.sync'.
    - Subscription WebSockets: canal store.{STORE_SKU}.printing + device.{DEVICE_ID}.notifications.
- Flux (V1)
    - API (server) crée PrintJob -> push queue -> broadcast “PrintJobQueued” (store.{SKU}.printing).
    - Gateway consomme et tente l’impression -> renvoie statut (completed/failed) via REST et/ou event.
- Offline
    - La Gateway persiste une petite queue locale (fichiers/SQLite) et rejoue à la reconnexion. *_jpensse sqlite c le mieux pour la V1 car on pourra faire des stats dessus et c plus robuste qu’un fichier_*

2) Approche B — Impression Cloud directe
- Description: L’API envoie directement à l’imprimante réseau (IP:9100) ou via un connecteur cloud.
- Avantages
    - Pas d’agent local à installer.
- Inconvénients
    - Fragile (NAT/Firewall, IPs changeantes), pas d’USB, latence, problèmes de sécurité réseau.
    - Peu de contrôle matériel (tiroir RJ11).
- Recommandation
    - ça peut être bien pour une v1 mais au long terme ça va être galère, surtout si on veut gérer des tiroirs-caisse.

Décision V1 (conseillée)
- Printer Gateway locale.

Architecture V1 (proposée)
- Backend Laravel
    - Crée des PrintJobs (DB + queue Redis).
    - Émet events via Soketi: “PrintJobQueued”, “PrintJobUpdated”.
    - Expose endpoints REST pour confirmation/échec depuis la Gateway.
- PrinterGateway (app locale)
    - Abonnements WebSockets: store.{STORE_SKU}.printing.
    - Poll de fallback (REST) si WS indispo.
    - Rendu: transforme le payload en commandes ESC/POS et envoie au périphérique (LAN/USB).
    - Retours: POST /v1/print-jobs/{id}/status { status, error? }.

Modèle de données (simplifié)
- PrintJob
    - id, store_id, type ('customer_receipt' | 'kitchen_ticket' | 'drawer_open' | 'report'),
    - payload (JSON) — données de ticket (lignes, totaux, codes-barres, QR…),
    - template (nom/version),
    - target_printer_id (facultatif, sinon imprimante par défaut par type),
    - status ('queued' | 'processing' | 'completed' | 'failed'),
    - attempts, last_error, created_at, updated_at.

Exemple de payload (JSON)
```json
{
  "type": "customer_receipt",
  "order": {
    "id": "ORD-2025-000123",
    "datetime": "2025-09-26T12:34:56Z",
    "items": [
      {"name": "Burger", "qty": 2, "price_cents": 12000, "modifiers": ["Cheese", "No onion"]},
      {"name": "Fries", "qty": 1, "price_cents": 4000}
    ],
    "subtotal_cents": 28000,
    "tax_cents": 0,
    "total_cents": 28000,
    "payments": [{"method": "cash", "amount_cents": 30000, "change_cents": 2000}]
  },
  "store": {"name": "Pizza Bab El Oued", "sku": "pizza-bab-el-oued"},
  "footer": "Merci de votre visite"
}
```

Canaux WebSockets (Soketi)
- store.{STORE_SKU}.printing: diffusion des nouveaux jobs et mises à jour.
- device.{DEVICE_ID}.notifications: pings/erreurs spécifiques à la Gateway.

Endpoints REST (brouillon)
- POST /v1/print-jobs
    - x-abilities: ['ticket.create'] (ou endpoint interne déclenché par Order/Receipt)
- GET /v1/print-jobs?status=queued&limit=50
    - x-abilities: ['printer.read']
- POST /v1/print-jobs/{id}/status
    - body: { status: 'completed'|'failed', error?: string, attempt?: number }
    - x-abilities: ['printer.update']

Templates d’impression
- V1: rendu côté Gateway (lib ESC/POS) ou pré-rendu textuel côté serveur (plus simple au début).
- Format recommandé: gabarit texte/markup minimal (ex: Blade->texte) + mapping ESC/POS (gras, align, QR).
- Tickets: client (80mm), cuisine (texte large, sans prix), rapport X/Z (V1.1).

Sécurité & permissions
- Abilities (config/abilities.php): 'printer.read','printer.update','ticket.create'.
- Token device scoppé PrinterGateway.
- Vérifier IP allowlist si activée sur le device.

Matériel recommandé
- Imprimantes: Epson TM-T20III LAN (80mm), XPrinter 80 LAN (budget).
- Tiroir-caisse: RJ11 branché à l’imprimante.
- Hôte Gateway: POS (si stable) ou mini PC fanless (NUC) / Raspberry Pi 4.
- Réseau: câble LAN pour imprimantes, routeur stable.

Roadmap V1.1
- RabbitMQ pour orchestration multi-sites/haute charge.
- Impression d’images/logos optimisées (bitmap ESC/POS), code-barres/QR avancés.
- Drivers Sunmi/Android SDK, découpe automatique, capteurs (papier).
- File locale persistante et reprise après crash, priorités de jobs.
