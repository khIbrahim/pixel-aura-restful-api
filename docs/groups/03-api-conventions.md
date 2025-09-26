# API Conventions (V1)

Objectif
- Standardiser les règles transverses de l’API pour V1 (headers, erreurs, pagination, idempotence, ETag, x-abilities).
- Aligner tous les services (Catalog, Orders, Payments, Media, Devices) sur les mêmes conventions.

Contenu prévu
- Auth & contexte
    - Auth: Bearer (Sanctum device), éventuellement membre via middleware store_member
    - En-têtes: X-Store-Sku, X-Device-Id, X-Device-Fingerprint
    - x-abilities par endpoint (dot-notation)
- Corrélation & idempotence
    - X-Correlation-Id obligatoire côté clients
    - Idempotency-Key pour mutations sensibles (orders, payments)
- Versionnement & cache
    - Version API /v1
    - ETag/If-None-Match sur endpoints “compacts” (catalog, KDS list)
    - menu_version pour invalidation cache client
- Requêtes
    - Accept: application/json; Timezone: UTC par défaut
    - Formats date/heure: ISO 8601, suffixe Z
    - Validation: 422 + détails par champ
- Réponses
    - Enveloppe JSON: { data, meta?, links? }
    - Pagination: page, limit (default 20, max 100), meta.total, links.prev/next
    - Erreurs: structure { error: { code, message, details?, correlation_id } }
- Filtres & tri
    - Query params: filter[field], sort=field|-field, fields=…
    - updated_since pour deltas (V1.1)
- Rate limiting & sécurité
    - Throttle per-device, anti-bruteforce (auth), IP allowlist (devices)
    - CORS minimal (origines connues des apps)
- Dépréciations
    - Deprecation & Sunset headers (V1.1), changelog
- Bonnes pratiques
    - Retours 201 + Location pour créations; 204 pour suppressions
    - Ne pas exiger totaux côté client (source de vérité serveur)
```
