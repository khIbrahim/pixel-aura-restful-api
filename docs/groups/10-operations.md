# Déploiement & Hébergement (V1)

Objectif
- Proposer une architecture Docker “légère” pour V1, simple à opérer sur un VPS.
- Démarrer avec docker-compose (pas Kubernetes), tout en séparant les services pour éviter un gros conteneur unique.
- Préparer l’évolution (S3 managé, DB managée) pour alléger encore la machine.

Stratégie générale
- Éviter “un seul gros conteneur”. Séparer en petits services: app, nginx, db, redis, websockets (Soketi), Minio (ou S3), queue worker, scheduler.
- Deux profils selon les moyens:
    1) Profil “small-prod” (recommandé V1): Nginx, App (PHP-FPM), Redis, MySQL, Soketi. Minio.
    2) Profil “managed” (plus léger sur le VPS): DB managée (RDS/Aurora/Scaleway), S3 managé (Cloudflare R2/S3). Sur le VPS: Nginx, App, Redis, Soketi seulement.

Specs serveur minimales (un restaurant, V1)
- VPS 2 vCPU / 4 Go RAM / 40–60 Go SSD (Debian/Ubuntu).
- Charge estimée (idle + trafic modéré):
    - Nginx ~20–40 Mo
    - PHP-FPM (app) ~150–300 Mo (selon workers)
    - Redis ~50–150 Mo
    - Soketi ~50–150 Mo
    - MySQL ~300–700 Mo (selon cache/buffer)
    - Minio ~150–250 Mo (si activé)
    - Meilisearch ~200–400 Mo (si activé)
- Cela tient aisément dans 4 Go si on n’active pas tout au début.

Topologie (réseau interne)
- Nginx reverse-proxy (80/443) ➜ App (php-fpm) sur réseau interne docker.
- App parle à: MySQL, Redis, Soketi, Minio, Meilisearch via noms de service docker.
- Printer Gateway: service local sur site (pas sur le serveur), il se connecte via WebSockets.

Conventions env (extrait)
- APP_ENV=production, APP_DEBUG=false, APP_URL=https://api.example.com
- DB_* => service “mysql” (ou DB managée)
- CACHE_STORE=redis, QUEUE_CONNECTION=redis, SESSION_DRIVER=redis
- BROADCAST_DRIVER=pusher (Soketi)
    - PUSHER_APP_ID=app
    - PUSHER_APP_KEY=localkey
    - PUSHER_APP_SECRET=localsecret
    - PUSHER_HOST=soketi
    - PUSHER_PORT=6001
    - PUSHER_SCHEME=http
    - PUSHER_APP_CLUSTER=mt1
- Filesystem: S3 ou Minio
    - Pour Minio: AWS_ENDPOINT=http://minio:9000, AWS_USE_PATH_STYLE_ENDPOINT=true
- Meilisearch (option): MEILISEARCH_HOST=http://meilisearch:7700

Déploiement — étapes
1) Construire l’image applicative (multi-stage, PHP-FPM alpine + extensions requises).
2) Lancer docker-compose.prod.yml (avec profils).
3) Exécuter migrations et storage:link.
4) Démarrer queue worker et scheduler.
5) Configurer le reverse proxy avec TLS (Caddy/Traefik/Nginx + Let’s Encrypt).
6) Vérifications: santé des services, latences WebSockets, impression via Gateway (site client).

Évolution
- Remplacer MySQL local par DB managée (moins de RAM, snapshots automatiques).
- Remplacer Minio par S3/R2 managé (soulage le disque).
- Meilisearch activé seulement si recherche “as-you-type” nécessaire.

Sécurité & opé
- Backups DB quotidiens (mysqldump + rotation).
- Sauvegarde fichiers (si Minio local) via lifecycle ou rsync distant.
- Logs applicatifs en STDOUT (capturés par docker), rotation côté VPS.
- Mises à jour: déploiement par tag d’image; downtime minimal via `docker compose up -d`.

Notes (prises de la discussion)
- V1 embarque Soketi maintenant.
- Printer Gateway reste côté restaurant (LAN), ne tourne pas sur le serveur V1.
- STORE_SKU standardisé; websockets et events pour orders/kds/printing activés.
