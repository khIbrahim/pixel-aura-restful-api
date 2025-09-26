# Provisioning des devices (V1)

Objectif
- Définir comment installer et sécuriser POS, Kiosk, KDS et Printer Gateway via tokens, empreinte (fingerprint) et WebSockets (Soketi).
- Standardiser les variables d’environnement par device et le pairing (QR).

Périmètre V1 (types de devices)
- POS (Caisse)
- Kiosk (Borne de commande)
- KDS (Écran cuisine)
- PrinterGateway (Passerelle d’impression locale)
  Remarque: Monnayeur en V1.1 (via POS/PrinterGateway).

Variables d’environnement (device)
- STORE_SKU=<sku_du_store>  // identifiant public standard
- DEVICE_TOKEN=<token_d’accès_device>  // Bearer
- DEVICE_FINGERPRINT=<empreinte_matérielle>  // stable par machine
- API_URL=https://api.exemple.com/api/v1  // base de l’API

Pairing et QR code
- Flux:
    1) Admin installe le store et émet un token device pour un type choisi (+ fingerprint).
    2) L’outil d’installation génère un QR code embarquant la config minimale.
    3) Le device scanne le QR, remplit le .env local et teste /me/abilities.
- Payload QR (base64 d’un JSON):
  ```json
  {
    "store_sku": "pizza-bab-el-oued",
    "device_token": "eyJhbGciOi...",
    "device_token_name": "Kiosk #1",
    "device_fingerprint": "fpr-abc-123",
    "api_url": "https://api.exemple.com/api/v1"
  }
  ```
- Sécurité QR:
    - Afficher le QR pendant une courte fenêtre.
    - Ne jamais réutiliser un token une fois “appliqué” sur un device.
    - Prévoir rotation simple depuis le back office.

Cycle de vie des tokens (Sanctum)
- Émission: via commande ou endpoint d’admin, abilities selon le type de device (voir “Abilities”).
- TTL: configurable par type (ex: 90 jours), renouvelable.
- Rotation: invalider l’ancien token et pousser le nouveau (via écran local ou back-office).
- Révocation: immédiate (ex: vol/perte), suppression de tous les tokens du device.
- Validation: contrôle du fingerprint et des abilities à chaque requête sensible.

Abilities par type (guide)
- POS: commandes (lecture/création/màj), paiements cash, remises/annuls, lecture menu, ticket, session/tiroir.
- Kiosk: lecture menu, création commande, ticket, pas de remises ni d’édition catalogue.
- KDS: lecture/maj tickets cuisine, changement statuts commande (prep -> ready -> served).
- PrinterGateway: lecture/config imprimantes, consommation de jobs d’impression, création de tickets.
  Note: la source de vérité des slugs d’abilities est config/abilities.php. Les endpoints exposés incluront GET /me/abilities pour auto-configurer les UIs.

Heartbeat, statut et IP allowlist
- Heartbeat (ex): POST /devices/{id}/heartbeat
    - body: { ip, systemInfo{} }
    - Met à jour last_heartbeat_at, last_seen_at, last_known_ip, reset des failed_auth_attempts
- Statut calculé côté serveur: online/offline/inactive/blocked.
- IP allowlist par device: si configurée, on refuse hors plage. _(déjà fait dans le store member, donc on va devoir faire un truc générique)._
- Anti-bruteforce: blocage automatique après X échecs (ex: 10) avec raison. _(déjà fait dans le store member, donc on va devoir faire un truc générique)._

Rate limiting (reco V1) _(déjà fait dans le store member, donc on va devoir faire un truc générique)._
- API lecture (menu, commandes): 120 req/min par device.
- Écriture (commande, ticket, paiement): 30 req/min par device.
- Heartbeat: 1 toutes 30–60s; server refuse <15s pour éviter le spam.
- WebSockets: 1 connexion par device, backoff exponentiel à la reconnexion.

WebSockets (Soketi)
- Hébergeur: Soketi (self-hosted) avec rabbit mq et pusher via Laravel Websockets.
- Nommage des canaux (exemples):
    - store.{STORE_SKU}.orders
    - store.{STORE_SKU}.kds
    - store.{STORE_SKU}.printing
    - device.{DEVICE_ID}.notifications
- Évènements clés:
    - OrderCreated|OrderUpdated
    - KdsTicketCreated|KdsTicketUpdated
    - PrintJobQueued|PrintJobCompleted|PrintJobFailed
- Usage:
    - Kiosk: s’abonne à store.{SKU}.orders pour retours serveur (ex: numéro de commande).
    - KDS: s’abonne à store.{SKU}.kds pour nouveaux bons.
    - PrinterGateway: s’abonne à store.{SKU}.printing pour consommer les jobs.

Scénarios d’installation (pas-à-pas)
- POS/Kiosk/KDS:
    1) Admin crée device token -> génère QR.
    2) App scanne QR -> écrit .env -> teste GET /me/abilities.
    3) App envoie heartbeat initial + récupère catalogue (ETag).
    4) App ouvre la connexion Soketi et s’abonne aux canaux utiles.
- PrinterGateway:
    - Identique + configure l’imprimante LAN/USB; s’abonne à store.{SKU}.printing; traite la file d’attente.

Roadmap V1.1 (aperçu)
- Pairing “tap-to-pair” local (LAN) et rotation silencieuse des tokens.
- Monnayeur (via POS/PrinterGateway).
- Renforcement MDM (enrôlement, statut parc, MAJ app version/firmware).

Annexes
- Conventions d’abilities: dot-notation (ex: categories.read), alignée avec config/abilities.php.
- Variables standardisées: STORE_SKU obligatoire (remplace tout “slug” historique).
