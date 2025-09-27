# Media & Images (V1)

Objectif
- Normaliser l’upload, le stockage et la diffusion d’images pour le catalogue (Catégories, Items) avec S3/Minio et Spatie MediaLibrary.
- Définir les endpoints V1 (upload/remplacement/suppression/liste), la sécurité (validation, domaines bloqués), et les variables d’environnement.

Architecture
- Stockage: S3/Minio (voir `config/filesystems.php`, disques `s3`, `minio`; docker-compose fournit le service Minio).
- Lib: Spatie MediaLibrary (conversions, responsive), services custom `MediaManager`, `ImageProcessor`, `MediaValidator`.
- Configs:
  - `config/media-management.php`: collections, conversions, validation (mimes/taille/dimensions), URL processing, sécurité.
  - `config/media-library.php`: en-têtes S3 (CacheControl), lifetime des URLs temporaires, responsive images.

Variables d’environnement clés
- S3 (Cloud) ou Minio (local):
  - S3: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_URL`, `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT=true`.
  - Minio: `MINIO_ACCESS_KEY`, `MINIO_SECRET_KEY`, `MINIO_REGION=us-east-1`, `MINIO_BUCKET`, `MINIO_ENDPOINT=http://localhost:9000`.
- Générales:
  - `FILESYSTEM_DISK=s3` (ou `minio` en dev)
  - `MEDIA_TEMPORARY_URL_DEFAULT_LIFETIME=5` (minutes, pour URLs temporaires)
  - `MEDIA_DOWNLOADER_SSL=true` (désactiver seulement en local si besoin)
- Docker Minio (déjà présent): Console sur 9001, S3 sur 9000.

## Conventions des collections & conversions

Pour tous les modèles supportant des médias (Category, Item, Option, Ingredient, OptionList, ItemVariant, etc.), deux collections sont standardisées:
- main_image (image principale)
- gallery (galerie d’images)

Chaque modèle enregistre les mêmes conversions synchrones (nonQueued):
```php
$this->addMediaConversion('thumbnail')
    ->width(300)
    ->height(300)
    ->nonQueued();

$this->addMediaConversion('banner')
    ->width(1200)
    ->height(600)
    ->nonQueued();

$this->addMediaConversion('icon')
    ->width(64)
    ->height(64)
    ->nonQueued();
```

Notes
- Disque par défaut: s3 (ou minio en dev).
- Les conversions sont disponibles pour les deux collections (main_image et gallery).
- La compatibilité avec des collections spécifiques (ex: category_images) peut rester pour rétrocompatibilité, mais la norme V1 est main_image + gallery sur tous les modèles.

---

Validation & sécurité (V1)
- Tailles: max 5 MB (voir `media-management.validation.max_size`).
- Mimes autorisés: jpg, jpeg, png, webp (extensibles).
- Dimensions min/max: 100x100 min; 5000x5000 max.
- URL externes (addMediaFromUrl): `url_processing.enabled=true` avec domaines bloqués par défaut (localhost & plages privées).
- Visibilité: public par défaut sur s3/minio; possibilité de passer en privé + URLs signées en V1.1.
- Logs structuré sur upload/remplacement; erreurs détaillées (message + fields).

Flux d’upload (V1)
1) Front envoie un fichier (multipart/form-data) ou une URL (si activé).
2) `MediaValidator` valide taille/mime/dimensions; `ImageProcessor` optimise.
3) Ajout via Spatie -> stockage sur disque (s3/minio) dans la collection ciblée.
4) Génération des URLs (origin + conversions); retour d’un `MediaResult` avec métadonnées.
5) Cache HTTP: Cache-Control côté S3 (ex: `max-age=31536000`), fingerprint par nom ou querystring.

## Endpoints REST (brouillon, génériques)

Routes génériques (déjà présentes) sous middleware: auth:sanctum, device.ctx, device.throttle:per-device, correlate, store_member.

Paramètres
- type: le type de ressource
    - Valeurs V1: categories | items | options | ingredients | option-lists | item-variants
- modelBinding: identifiant de la ressource (id ou binding Eloquent)
- media: identifiant media (pour suppression ciblée en galerie)

Collections “main”
- GET /v1/{type}/{modelBinding}/media/main
    - But: retourner l’image principale et ses URLs de conversions.
    - x-abilities: lecture du type (ex: categories.read, item.read, option.read, ingredient.read, option_list.read)
- POST /v1/{type}/{modelBinding}/media/main
    - But: créer/remplacer l’image principale.
    - Consumes: multipart/form-data (file) OU application/json { "url": "https://..." } si url_processing.enabled.
    - x-abilities: mise à jour du type (ex: category.update, item.update, option.update, ingredient.update, option_list.update)
- DELETE /v1/{type}/{modelBinding}/media/main
    - But: supprimer l’image principale (respecte cleanup_old_media).
    - x-abilities: mise à jour du type (ex: category.update, item.update, ...)

Collections “gallery”
- GET /v1/{type}/{modelBinding}/media/gallery
    - But: lister la galerie + métadonnées et URLs.
    - x-abilities: lecture du type (ex: categories.read, item.read, ...)
- POST /v1/{type}/{modelBinding}/media/gallery
    - But: ajouter une image à la galerie.
    - Consumes: multipart/form-data (file) OU application/json { "url": "https://..." } si url_processing.enabled.
    - x-abilities: mise à jour du type
- DELETE /v1/{type}/{modelBinding}/media/gallery/{media}
    - But: supprimer une image précise de la galerie.
    - x-abilities: mise à jour du type

Réponses (schémas recommandés)
- Main image
```json
{
  "data": {
    "id": 12345,
    "collection": "main_image",
    "disk": "s3",
    "file_name": "item-123.webp",
    "mime_type": "image/webp",
    "size_bytes": 482133,
    "width": 800,
    "height": 800,
    "created_at": "2025-09-26T12:34:56Z",
    "urls": {
      "original": "https://cdn.example.com/.../item-123.webp",
      "thumbnail": "https://cdn.example.com/.../conversions/item-123-thumbnail.webp",
      "banner": "https://cdn.example.com/.../conversions/item-123-banner.webp",
      "icon": "https://cdn.example.com/.../conversions/item-123-icon.webp"
    }
  }
}
```
- Gallery
```json
{
  "data": [
    {
      "id": 9876,
      "collection": "gallery",
      "disk": "s3",
      "file_name": "item-123-1.webp",
      "mime_type": "image/webp",
      "size_bytes": 301221,
      "width": 800,
      "height": 800,
      "created_at": "2025-09-26T12:35:10Z",
      "urls": {
        "original": "https://cdn.example.com/.../item-123-1.webp",
        "thumbnail": "https://cdn.example.com/.../item-123-1-thumbnail.webp",
        "banner": "https://cdn.example.com/.../item-123-1-banner.webp",
        "icon": "https://cdn.example.com/.../item-123-1-icon.webp"
      }
    }
  ]
}
```

Payload “images” standard dans les resources
- Exemple de structure exposée par getImagesAttribute (à implémenter via MediaResource):
```json
{
  "images": {
    "main": {
      "id": 12345,
      "file_name": "item-123.webp",
      "mime_type": "image/webp",
      "size_bytes": 482133,
      "width": 800,
      "height": 800,
      "urls": {
          "created_at": "2025-09-26T12:34:56Z",
          "original": "https://cdn.example.com/.../item-123.webp",
          "thumbnail": "https://cdn.example.com/.../item-123-thumbnail.webp",
          "banner": "https://cdn.example.com/.../item-123-banner.webp",
          "icon": "https://cdn.example.com/.../item-123-icon.webp"
      }
    },
    "gallery": [
      {
        "id": 9876,
        "file_name": "item-123-1.webp",
        "mime_type": "image/webp",
        "size_bytes": 301221,
        "width": 800,
        "height": 800,
        "created_at": "2025-09-26T12:35:10Z",
        "urls": {
            "original": "https://cdn.example.com/.../item-123-1.webp",
            "thumbnail": "https://cdn.example.com/.../item-123-1-thumbnail.webp",
            "banner": "https://cdn.example.com/.../item-123-1-banner.webp",
            "icon": "https://cdn.example.com/.../item-123-1-icon.webp"
        }
      }
    ]
  }
}
```

Notes d’implémentation
- MediaResource: centraliser le mapping Media -> JSON (id, file_name, mime_type, size_bytes, width/height si dispo, created_at, urls).
- Upload par URL: respecter `config/media-management.url_processing` (timeouts, domaines bloqués).
- ETag/If-None-Match sur GET pour optimiser le cache front (Kiosk/POS/Back Office).
- Abilities:
    - Lecture: categories.read | item.read | option.read | ingredient.read | option_list.read
    - Écriture: category.update | item.update | option.update | ingredient.update | option_list.update

CDN & Cache
- S3/Minio peuvent être derrière un CDN (Cloudflare/CloudFront).
- Headers `CacheControl` déjà configurés (`media-library.remote.extra_headers`).
- Reco: versionner les chemins (ex: `stores/{store_id}/items/{id}-{slug}/...`) pour éviter le cache stale après remplacement.

Erreurs & diagnostics
- 400: fichier invalide (mime/taille/dimensions), URL bloquée/domaine interdit.
- 413: payload trop volumineux.
- 415: type non supporté.
- 422: validations spécifiques (dimensions).
- 500: erreur interne (upload/transformation).
- Journalisation: succès/échec avec model type, id, collection, media_id/erreur.

Sécurité & permissions
- Abilities de mise à jour requises (category.update / item.update).
- Les devices Kiosk/KDS n’ont pas accès écriture (lecture uniquement).
- Back Office et POS (Manager/Owner) peuvent gérer les médias selon rôle.

Bonnes pratiques front (Kiosk/Back Office)
- Afficher des placeholders et charger `thumbnail` en liste; `medium` en détail.
- Lazy-load, `srcset` si responsive activé.
- Préférer WebP; fallback PNG/JPEG si besoin (V1.1).

Roadmap V1.1
- URLs signées pour disques privés; accès temporisé.
- Scan antivirus (ClamAV) et stripping EXIF.

Annexes
- `config/media-management.php` centralise la validation et les conversions par collection.
- Minio docker-compose: Console http://localhost:9001, S3 http://localhost:9000.
