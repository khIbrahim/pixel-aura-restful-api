# Offline & Sync (V1)

Objectif
- Permettre à Kiosk et POS de fonctionner sans réseau (prise de commande, encaissement cash, impression locale).
- Garantir une synchronisation fiable au retour réseau (idempotence, résolution de conflits, mapping local→serveur).
- Minimiser la charge serveur via ETag/If-None-Match, versions de menu, et WebSockets pour la réactivité.

Portée V1
- Apps: Kiosk (PWA), POS (PWA/desktop). KDS fonctionne principalement online (affichage live); fallback “refresh périodique” toléré.
- Paiement: cash uniquement. Carte (PayPart) en V1.1 nécessite online au moment du paiement.
- Impression: tickets client/cuisine peuvent être imprimés offline via Printer Gateway locale.

Principes généraux
- Device-first, Member-second:
    - Le device s’authentifie avec DEVICE_TOKEN/DEVICE_FINGERPRINT.
    - Le store member (si nécessaire) s’authentifie ensuite; la session membre est locale au device et resynchronisée.
- Écriture offline via queue locale (IndexedDB). Idempotency-Key pour chaque mutation.
- Lecture avec caches à jour conditionnel (ETag/If-None-Match) + menu_version.

Données à mettre en cache (clients)
- settings: STORE_SKU, API_URL, token, fingerprint, device settings.
- abilities: résultat de /me/abilities (TTL court).
- catalog compact:
    - categories_compact, items_compact (mini payload pour Kiosk).
    - ETag/If-None-Match, menu_version (Store).
- medias: URLs déjà résolues (avec Cache-Control côté S3/CDN).
- members (optionnel POS): liste minimale pour login rapide (code + nom + role), sans PIN.

Versionnement & ETag
- Catalog:
    - Inclure menu_version dans les réponses. Si menu_version a changé: invalider cache local.
    - GET /v1/catalog/compact?channel=kiosk avec If-None-Match: si 304, garder le cache.
- Abilities:
    - Vérifier périodiquement (ex: toutes 15 min) /me/abilities; sinon rafraîchir à l’ouverture de session.

Queue locale (IndexedDB)—structure
- stores:
    - settings: { store_sku, api_url, device_id?, device_token, updated_at }
    - cache.catalog: { etag, menu_version, payload, updated_at }
    - cache.abilities: { payload, updated_at }
    - queue.outbound:
  ```json
  [
      {
      id: 'uuid',
      created_at: ts,
      method: 'POST'|'PATCH'|'DELETE',
      path: '/v1/orders',
      body: { ... },
      headers: { 'Idempotency-Key': '...' },
      attempts: 0,
      parent_id?: 'uuid', // dépendance (ex: payment dépend de l’order)
      type: 'order.create'|'payment.capture'|'order.status.set'|...,
      local_refs: { order_local_id: '...' }, // mapping temporaire
      last_error?: { code, message, ts }
      }
  ]
  ```
    - map.local_to_server: {
      orders: { '<local_id>': { order_id: 123, mapped_at: ts } },
      payments: { '<local_id>': { payment_id: 456 } }
      }
    - telemetry: journaux à uploader (erreurs réseau, latences, diagnostics).

Idempotence & mapping local→serveur
- Chaque mutation a un Idempotency-Key (UUID stable) généré à la création de la tâche locale.
- Création de commande:
    - Le client génère order_local_id (UUID).
    - Serveur répond avec { order_id, local_id }.
    - Stocker le mapping local_id→order_id.
- Paiement:
    - Référence l’order par order_id si déjà mappé, sinon parent_id = task de création. Le worker attend que parent soit “succeeded”.

Protocole de synchronisation (boucles)
- Au boot:
    - Charger settings, abilities (si frais), cache catalog.
    - Si offline: activer le mode dégradé (pas d’API); impression locale OK.
- Pull (lecture):
    - Si online: GET /v1/catalog/compact avec If-None-Match (ETag). 200 => mettre à jour; 304 => rien.
    - Refaire toutes 5–15 min, ou sur trigger “menu_version changed” via WebSockets.
- Push (écriture):
    - Si online: dépiler queue.outbound en FIFO, en respectant les dépendances parent_id.
    - Exponential backoff (1s, 2s, 5s, 10s, 30s, 60s...) + jitter; max 10 essais avant statut “needs_attention”.
    - Sur succès: retirer tâche, mettre à jour mapping (local→server).
- Reconnexion:
    - À la première reconnexion après offline: lancer un cycle “push puis pull”, puis resouscrire WebSockets.

Politique d’erreurs (résumé)
- Réseau (timeout/DNS): retry (backoff).
- 401 Unauthorized: re-auth device (token expiré). Bloquer push, autoriser prise de commande locale mais prévenir l’utilisateur.
- 403 Forbidden: ability manquante. Marquer tâche “failed”; notifier UI pour action manuelle.
- 409 Conflict (idempotence): considérer comme succès; récupérer ressource serveur et compléter mapping.
- 422 Validation: le menu a changé; forcer refresh catalog; proposer de corriger la commande.
- 423 Locked/IP refusée: marquer device “needs_attention”; requérir admin.

Conflits & règles V1
- Item désactivé/épuisé depuis la dernière synchro:
    - Refuser la création serveur (422). Kiosk devra rafraîchir le catalog et proposer alternatives.
- Totaux côté client vs serveur:
    - Source de vérité: serveur. Client n’envoie pas les totaux finaux (ou bien seulement informatifs). Le serveur recalcule.
- Paiement cash offline:
    - Autorisé. Le reçu client est imprimé localement. À la synchro, si la commande ne peut pas être créée (ex: item désactivé), on marque la vente “needs_review” et on émet une alerte back-office.

Impression offline
- Le Printer Gateway imprime sans serveur (ticket client/cuisine).
- Lors de la synchro:
    - Si order créée: créer PrintJob “synced” (optionnel) ou ne rien émettre (éviter duplicata papier).
    - Conserver une trace locale du ticket imprimé avec le local_id/horodatage.

WebSockets (Soketi) & dégradation
- Online:
    - S’abonner: store.{STORE_SKU}.orders, .kds, .printing.
    - Actualiser UI en temps réel (OrderUpdated, KdsTicketUpdated...).
- Offline:
    - Suspendre WS; conserver “lastReceivedAt”.
- Reconnexion:
    - Rejouer la queue; puis faire un GET des ressources clés modifiées depuis “lastReceivedAt” (ou un simple refresh compact) pour se réaligner.

Sécurité & stockage local
- Chiffrage du storage local selon plateforme (recommandé sur POS desktop).
- Ne jamais stocker le PIN membre en clair. Seul le code opérateur est cacheable; le PIN est saisi à chaque login.
- Token device:
    - Rotation planifiée (TTL) → si token expiré en offline, on peut continuer à saisir des commandes, mais la synchro devra attendre re-provisionnement.

Exemples de tâches (queue.outbound)
```json
{
  "id": "f2ac8b0f-7c2a-4a22-bfc0-2e6e8ed6e0d1",
  "created_at": 1695741234000,
  "method": "POST",
  "path": "/v1/orders",
  "headers": { "Idempotency-Key": "f2ac8b0f-7c2a-4a22-bfc0-2e6e8ed6e0d1" },
  "type": "order.create",
  "body": {
    "channel": "kiosk",
    "local_id": "ord-local-8b8b-…",
    "items": [
      { "item_id": 345, "qty": 2, "variant_id": 2, "options": [92] }
    ],
    "notes": "Sans oignon"
  },
  "attempts": 0
}
```
Réponse attendue (succès):
```json
{
  "data": {
    "order_id": 123456,
    "local_id": "ord-local-8b8b-…",
    "status": "accepted",
    "totals": { "subtotal_cents": 28000, "tax_cents": 0, "total_cents": 28000 }
  }
}
```

Stratégies UI (Kiosk/POS)
- Indicateur de connectivité: online/offline/syncing.
- File locale visible (nombre de commandes en attente).
- Bouton “forcer la synchro” avec diagnostic (erreurs récentes).
- Messages clairs:
    - “Menu mis à jour. Veuillez vérifier votre panier.”
    - “Votre commande est enregistrée et sera synchronisée dès le retour du réseau.”
- Impression:
    - (Kiosk) Toujours imprimer le ticket client localement après validation, même offline.
    - (POS) Autoriser impression et ouverture tiroir en offline; journaliser mouvement.

Tests & validation
- Matrice:
    - Offline dès l’ouverture (aucun cache) → refuser la prise de commande jusqu’au premier fetch (sauf si cache catalog présent).
    - Perte réseau pendant commande → finaliser localement, queue la création.
    - Perte réseau pendant paiement → paiement cash accepté localement; synchro ultérieure.
    - Conflits de menu → 422; UI propose refresh.
- Tests de charge:
    - 100 commandes offline, synchro en rafale → vérifier idempotence et ordre (create → pay → print).

Implémentation côté serveur (rappels)
- Idempotence: support de l’entête Idempotency-Key pour orders/payments.
- ETag: sur endpoints catalogs compacts; If-None-Match 304.
- Événements:
    - OrderCreated/Updated, KdsTicketUpdated, PrintJobQueued via Soketi (store.{SKU}.…).
- Heartbeat device:
    - POST /devices/{id}/heartbeat: tolérer absence pendant offline, remettre à jour au retour.

Roadmap V1.1
- Delta sync (changements incrémentaux catalog/items par updated_at > last_sync).
- Reconciliation avancée (rapprochement cash vs ventes, anomalies).
- Upload télémetrie/erreurs (batch) avec quotas.
- “Tap-to-pair” local (LAN) et rotation de token silencieuse.
- Support carte (PayPart) avec gestion offline limitée (pré-autorisations non supportées).
