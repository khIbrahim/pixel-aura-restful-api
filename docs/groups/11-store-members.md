# Store Members (V1) — rôles, permissions, authentification, sessions

Objectif
- Décrire le fonctionnement V1 des Store Members (rôles, permissions, login PIN/code).
- Poser les bases d’un système robuste pour V1.1 (sessions détaillées, traçabilité, paie, “trous de caisse”).
- Aligner avec la sécurité devices/abilities et le middleware `store_member`.

Contexte et état actuel
- c’est l’un des premiers modules que que j'ai codé. Il est “compliqué” ou "bazar" mais il marche (authentification opérationnelle).
- Les rôles actuels sont provisoires (Owner, Manager, Cashier, Kitchen via StoreMemberRole).
- Les permissions sont “perso” (champ `permissions` sur StoreMember), extensibles par un supérieur + intégration avec `config/abilities.php`.
- Code clé:
    - Modèle StoreMember: champs `pin_hash`, `code_number`, `role`, `permissions`, `failed_attempts`, `locked_until`, helpers `checkPin`, `checkCode`, `code()` (format EMP-001… via préfixe rôle).
    - Relation `counter()`: lien vers StoreMemberCounter (séquence par rôle). Améliorable (cf. propositions).
- Middleware `store_member`: pivote la requête dans le contexte membre authentifié côté device (POS/Kiosk/KDS).

Rôles & permissions (V1)
- Rôles provisoires (StoreMemberRole): Owner, Manager, Cashier, Kitchen.
- Abilities: source de vérité dans `config/abilities.php` (dot-notation).
    - Rôles -> groupes -> abilities (dépliage).
    - Abilities “membre” majeures: `members.auth`, `members.logout`, plus les abilities métier (‘orders.*’, ‘payment.capture’, etc.).
- Permissions “perso” (StoreMember.permissions):
    - Tableau d’overrides au niveau membre (peut contenir `*`).
    - Un supérieur (Owner/Manager) peut ajouter/retirer une permission ponctuelle.

Authentification (V1)
- Hypothèse et recommandation: “Device-first, Member-second”
    1) Le device s’authentifie via Sanctum (DEVICE_TOKEN + DEVICE_FINGERPRINT).
    2) Le membre s’authentifie sur ce device (login code + PIN) pour obtenir une session membre locale au store/device.
- Identifiants:
    - Code opérateur lisible: format `PREFIX-###` (ex: EMP-001) via `StoreMember::code()`.
    - PIN secret: stocké en `pin_hash` (BCrypt via `Hash`), vérifié par `checkPin`.
- Flux de login POS (recommandé):
    - POST /v1/members/login { code: "EMP-001", pin: "1234" }
    - Vérifs: membre actif `is_active`, `locked_until` null/expiré; `failed_attempts` < seuil; `store_id` concordant avec le device; `role` autorisé sur ce device.
    - Succès:
        - Réinitialiser `failed_attempts`.
        - Émettre un “member session token” court (scopé au device et au store) ou associer la session au device (cf. Sessions).
        - Retourner `me` + `abilities` (résolues: rôle + permissions “perso”).
    - Échec:
        - Incrémenter `failed_attempts`, poser `locked_until` après N échecs.
        - Journaliser (activitylog) tentative échouée (IP device, heure).
- Logout:
    - POST /v1/members/logout (invalide la session membre/retire le membre du contexte).
- KDS:
    - Peut permettre `members.auth` minimal (ex: chef) ou fonctionner sans login membre (au choix du store).

Middleware `store_member` (comportement attendu)
- Contexte requis: device authentifié + store résolu (via `device.ctx`).
- Option 1 (V1 simple): session membre en mémoire côté serveur (cache/Redis) indexée par: store_id + device_id.
- Option 2 (recommandée): session membre matérialisée en DB (table sessions — cf. V1.1), avec TTL et “last_seen_at”, attachée au device_id.
- Effet: hydrate `$request->attributes['store_member']` (ou guard) pour policies/services.

StoreMemberCounter & code opérateur
- État actuel: relation `counter()` basée sur `store_id` + `role`. `code_number` formaté avec préfixe rôle.
- Propositions d’amélioration:
    - Atomicité: générer `code_number` via une transaction + verrou de ligne (FOR UPDATE) pour éviter les doublons en concurrence.
    - Table dédiée `store_member_counters` (clé composite store_id+role) avec `next_number`.
    - Validation: unicité `(store_id, role, code_number)` + index.

Sécurité (V1)
- Anti-bruteforce: `failed_attempts` + `locked_until` (déjà dans StoreMember).
    - Seuils recommandés: 5 (avertissement), 10 (lock 15–30 min).
- Rate limit par device sur auth membres (middleware `device.throttle:per-device`).
- Contrainte de périmètre:
    - Un membre ne peut opérer que sur son `store_id`.
    - Un device ne peut “porter” qu’une session membre à la fois (POS); en KDS, option multi (mais déconseillé).
- Audit (Spatie):
    - Saisir: login success/failure, logout, changement PIN, création/suspension membre, actions sensibles (refund, drawer.payout/payin).
- Changement PIN:
    - POST /v1/members/{id}/pin { current_pin, new_pin } (Owner/Manager peut forcer).
    - Mettre à jour `pin_hash`, `pin_last_changed_at`.

Sessions Membres (V1 ➜ V1.1)
- V1 (minimal):
    - Session volatile côté serveur (cache) + horodatage `last_login_at` et `login_count` (sur StoreMember).
    - “Current shift” POS côté caisse (ouvert/fermé) géré par abilities `session.open/close` (déjà dans config), distinct de la session “membre”.
- V1.1 (recommandé):
    - Table `store_member_sessions`:
        - id, store_id, device_id, member_id, started_at, ended_at, duration, last_seen_at, status ('active'|'closed'), meta (JSON: version app, IP, fingerprint device).
    - Événements:
        - MemberSessionStarted/Ended, DrawerOpened/Payout/Payin, RefundProcessed.
    - KPIs/RH:
        - Heures de présence, actions par session (nb ventes, montants, remboursements).
        - “Trous de caisse”: comparer mouvements cash vs total ventes/versements session.
    - Conformité:
        - Journal inviolable (append-only ou audit renforcé).

Remplacements de tâches physiques (vision produit)
- POS: ouverture/fermeture de caisse, comptage en fin de journée, justificatifs (tickets, exports).
- RH: pointage par session membre (arrivée/départ), suivi activité (ventes, remboursements).
- Contrôle interne: seuils d’alerte (remises anormales, remboursements fréquents), double validation (futur).

Services Store Member (refactor)
- Constats: “il y en a beaucoup inutiles”. Cible V1:
    - AuthenticationService (login/logout PIN, change PIN).
    - SessionService (V1.1): start/end, heartbeat, stats.
    - PermissionResolver: fusion rôle + permissions “perso” + abilities device (intersection).
    - MemberAdminService: CRUD membre, activation/suspension, reset PIN, rôle, permissions.
- Guidelines:
    - Séparer “application services” de la persistance (Repository interfaces).
    - Centraliser la résolution des abilities (éviter duplications).

Intégration avec Abilities & Devices
- Principe de moindre privilège:
    - “Abilities effectives” = intersection entre le set du membre et le set du device.
    - Exemple Kiosk: même si un membre a `order.discount`, le Kiosk n’expose pas cette action.
- GET /api/v1/me/abilities:
    - Pour device-only (pas de membre), retour abilities device.
    - Pour device + membre, retour abilities finales (intersection) et rôle du membre.

Endpoints (blueprint — OpenAPI viendra après)
- POST /v1/members/login  (x-abilities: ['members.auth'])
- POST /v1/members/logout (x-abilities: ['members.logout'])
- GET  /v1/members/me     (retour profil + rôle + permissions + abilities résolues)
- POST /v1/members/{id}/pin (change/reset PIN; x-abilities: ['members.update'] ou self-service)
- CRUD membres (Back Office):
    - GET/POST/PATCH/DELETE /v1/members [...] (Owner/Manager via '@members.manage')
- V1.1 sessions:
    - POST /v1/member-sessions/start|end
    - GET  /v1/member-sessions?filters…
    - GET  /v1/member-sessions/{id}

Données exposées (profil membre)
```json
{
  "id": 123,
  "store_id": 7,
  "name": "Amine",
  "role": "Cashier",
  "code": "EMP-014",
  "is_active": true,
  "last_login_at": "2025-09-26T13:40:00Z",
  "login_count": 42,
  "permissions": ["order.create","payment.capture"], 
  "abilities": ["order.create","payment.capture","ticket.create", "...resolved..."]
}
```

Roadmap & tâches
- V1 (immédiat):
    - Intersection abilities device+membre; endpoint `/me/abilities`.
    - Journaliser login/logout/échecs.
- V1.1:
    - Table `store_member_sessions` + métriques (durée, ventes, cash).
    - Rapprochement “trou de caisse” (drawer vs ventes), alertes.
    - Administration fine des permissions “perso” (UI).
    - Nettoyage/services inutiles, modularisation (Auth, Sessions, Admin, Resolver).

Risques & garde-fous
- Confusion device vs membre: toujours contextualiser les requêtes par store+device; imposer l’intersection d’abilities.
- Sécurité PIN: exiger min 4–6 chiffres, rotation obligatoire périodique (option), limiter le “brute force”.
- Continuité offline: POS/Kiosk doivent conserver le membre courant en mémoire locale (avec TTL court) et revalider au retour réseau.
