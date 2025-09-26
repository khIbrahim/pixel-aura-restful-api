# Abilities & Auth (V1)

Objectif
- Normaliser les “abilities” (permissions fines) pour devices et Store Members.
- Documenter la source de vérité (config/abilities.php), la résolution (groupes, rôles), l’exposition API (/me/abilities), et l’usage côté services/middlewares.
- Aligner la doc des endpoints avec x-abilities (dot-notation).

Terminologie
- Ability: permission atomique (ex: `order.create`, `printer.update`).
- Group: alias d’un ensemble d’abilities (peut référencer d’autres groupes via `@group`).
- Role: ensemble d’abilities (et/ou groupes) affecté à un Store Member selon son rôle (Owner, Manager, Cashier, Kitchen).
- Device abilities: abilities attachées au token du device (Sanctum) selon son type (POS, Kiosk, KDS, PrinterGateway).

Source de vérité: config/abilities.php
- version: 1 (registry versionning).
- abilities: dictionnaire des slugs dot-notation -> description lisible (UI/Back Office).
- groups: ensembles nommés. Référencer un groupe avec `@group.name`.
- roles: mappage StoreMemberRole -> [abilities|@groups].
- expose:
    - me_abilities_route: `/api/v1/me/abilities`
    - cache: key `abilities.registry.v1`, ttl 3600s

Convention
- Slugs en dot-notation uniquement (ex: `categories.read`, `order.create`). Éviter la notation “categories:read”.
- Un endpoint documenté doit déclarer ses requirements via `x-abilities: [ '...' ]`.

Résolution des abilities
1) On part de la liste abilities déclarées + groupes + rôles.
2) Dépliage des groupes: `@orders.base` => remplace par toutes ses abilities (récursif).
3) Rôles: Owner/Manager/Cashier/Kitchen héritent d’ensembles prédéfinis (cf. config).
4) Dédupliquer le set final (Set semantics).
5) Device: le token Sanctum porte ses propres abilities (ex: Kiosk n’a pas `order.discount`).

Exposition API — GET /api/v1/me/abilities
- Route: définie dans `config/abilities.php['expose']['me_abilities_route']`
- Auth: Bearer (Sanctum) — device ou store member.
- Réponse (exemple):
```json
{
  "version": 1,
  "subject": {
    "type": "device", 
    "id": 42, 
    "device_type": "Kiosk", 
    "store_sku": "pizza-bab-el-oued"
  },
  "abilities": [
    "menu.read",
    "item.read",
    "order.create",
    "ticket.create",
    "device.sync",
    "kiosk.config.read"
  ],
  "roles": [], 
  "resolved_at": "2025-09-26T13:36:48Z",
  "cache": { "hit": true, "ttl": 3600 }
}
```
- Pour un Store Member (POS), `subject.type = "member"`, inclut `role` et éventuels `permissions` spécifiques s’ils existent.

Enforcement (serveur)
- Sanctum token abilities:
    - Vérifier via `ability:{ability}`.
    - Devices: abilities injectées lors de l’émission du token (DeviceTokenService).
- Store Members:
    - Modèle `StoreMember` expose `hasPermission($ability)`:
        - Permissions explicites via champ `permissions` (array) ou wildcard `*`.
        - Rôle => abilities via registry (groupes dépliés).
- Middlewares conseillés:
    - auth:sanctum
    - device.ctx (contexte store/device)
    - device.throttle:per-device (rate limit)
    - store_member (hydrate le membre si applicable)
    - Un middleware “ability:$slug” (ou Gates/Policies) peut être créé pour sécuriser les routes.

### x-abilities dans la spécification OpenAPI
- Chaque path/method doit référencer les abilities requises (dot-notation):
```yaml
x-abilities:
  - order.create
  - ticket.create
```

Cache & invalidation
- Registry cache key: `abilities.registry.v1` (TTL 3600s).
- Invalidation recommandée:
  - bump de `version` si breaking change,
  - flush du cache abilities après déploiement de nouveaux slugs/groupes/roles.

Sécurité complémentaire
- Rate limiting par device (middleware `device.throttle:per-device`).
- IP allowlist par device (voir Device model).
- Blocage automatique après tentatives d’auth multiples (voir Device::recordFailedAuth()).
- Audit des actions sensibles (Spatie Activitylog), ex: `device.token.issue`, `order.refund`.

Annexes
- Rôles par défaut (StoreMemberRole):
    - Owner: accès étendu (gestion magasin, devices, ventes, taxes, etc.).
    - Manager: proche Owner mais borné (ex: pas de RGPD).
    - Cashier: vente & caisse (pas de gestion catalogue).
    - Kitchen: KDS, lecture menu, tickets cuisine.
- Migration: remplacer toute notation `categories:read` par `categories.read` dans les specs.
