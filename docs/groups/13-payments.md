# Paiements — Algérie (V1 cash, V1.1 PayPart)

Objectif
- Définir les flux d’encaissement V1 (cash only) alignés avec POS/Kiosk/KDS et Printer Gateway.
- Cadrer l’idempotence, les sessions de caisse (tiroir), les tickets et la résilience offline.
- Préparer l’intégration carte PayPart (V1.1) avec abstraction Payment Provider + webhooks.

Portée V1
- Méthodes: cash uniquement.
- Canaux: POS (principal), Kiosk (optionnel: ticket “payer à la caisse”), Website (click&collect, paiement à la collecte).
- Tickets: client et cuisine via Printer Gateway.
- Sessions de caisse et tiroir: open/close, drawer.open, drawer.payout/payin.
- Offline-first: encaissement cash possible hors-ligne, synchronisation au retour réseau.

Abilities impliquées (référence config/abilities.php)
- Paiements: 'payment.capture' (V1), 'payment.refund' (V1.1 recommandé)
- Sessions & tiroir: '@session.cash' => 'session.open','session.close','drawer.open','drawer.payout','drawer.payin','fiscal.z-export' (Z en V1.1)
- Tickets: 'ticket.create'
- Commandes: '@orders.base','order.status.set','order.reprint'
- Audit & ventes: 'sales.read' (rapports), 'audit.read' (V1.1)

Modèle conceptuel (V1)
- Payment
    - id, order_id, method: 'cash', amount_cents, change_cents, status: 'captured'|'void'|'refunded' (V1 surtout 'captured')
    - captured_by_member_id, captured_at, device_id
    - idempotency_key, meta {}
- CashMovement (journal caisse)
    - id, store_id, session_id, type: 'open'|'close'|'payout'|'payin'|'sale_change'|'correction'
    - amount_cents (positif pour entrée, négatif pour sortie), reason?, device_id, member_id, created_at
- Session (POS)
    - id, store_id, device_id, opened_by_member_id, opened_at, closed_at?, expected_cash_cents?, counted_cash_cents?, delta_cents?
    - status: 'open'|'closed'
- Receipt (liés à PrintJob)
    - id, order_id, type: 'customer'|'kitchen', printed_at?, print_job_id?

Règles de calcul (V1)
- change_cents = max(0, amount_cents - order.total_cents)
- À la capture:
    - créer Payment(cash, status='captured')
    - créer CashMovement(type='sale_change', amount = -change_cents) pour tracer la sortie de caisse du rendu monnaie
- Taxes:
    - si store.tax_inclusive = true: total TTC envoyé au client; tax_cents dérivé informativement
    - si false: total = subtotal + tax_cents (V1: simple)
- Arrondis: DZD (pas de décimales en espèces). Conserver les montants en cents (x100) pour cohérence interne.

Flux POS (V1)
1) Création commande (accepted)
2) Capture paiement cash:
    - POST /v1/orders/{id}/payments { method: "cash", amount_cents, idempotency_key }
    - Server:
        - recalcule totaux
        - crée Payment, calcule change_cents
        - crée CashMovement (sale_change négatif si rendu monnaie)
        - émet PrintJob ticket client
        - Order status: 'accepted' -> 'in_preparation' (si cuisine) sinon 'served'/'picked_up'
3) Tiroir:
    - drawer.open pour remettre la monnaie (imprimante RJ11)
4) Session:
    - session.open à l’ouverture de poste
    - session.close en fin de journée (compter caisse)

Flux Kiosk (V1)
- Deux options:
    1) “Payer à la caisse”:
        - Kiosk crée Order (accepted), imprime ticket client “à payer”
        - POS retrouve la commande et capture le cash
    2) (Plus tard) Kiosk capture cash? Non — réservé au POS en V1 (meilleure maîtrise).

Offline (V1)
- POS offline:
    - Peut créer commande locale + paiement cash local; imprime ticket client
    - À la synchro:
        - push CreateOrder (idempotent), puis Payment(cash)
        - si item indisponible entre-temps → flag “needs_review” (Back Office)
- Kiosk offline:
    - Crée commande locale, imprime ticket “à payer”, synchronise à la reconnexion

Idempotence (cruciale)
- Toute capture envoie Idempotency-Key unique (UUID) par paiement
- Si retry réseau: 200 idempotent avec même payment_id
- Conflit (409) => récupérer le Payment existant et le retourner au client

Tickets (Printer Gateway)
- Ticket client (80mm):
    - Store name/sku, date/heure, lignes, quantités, totaux, paiement cash, rendu monnaie, message pied
- Ticket cuisine:
    - Sans prix; lignes + modificateurs; numéro/identifiant de commande
- Réimpression: 'order.reprint' (loguer la réimpression)

Journal de caisse (CashMovement) — V1
- À chaque rendu monnaie: enregistrer sortie (sale_change)
- Payout/Payin:
    - 'drawer.payout' (sortie pour dépenses), 'drawer.payin' (entrée)
    - Requiert motif (reason) et permission
- Session close:
    - Saisir counted_cash_cents, calculer delta_cents = counted - expected
    - Sauvegarder pour analyse “trous de caisse”

Erreurs & sécurité
- 401: device non authentifié / token expiré
- 403: manque d’ability (payment.capture, drawer.*)
- 409: idempotency conflict → renvoyer le payment existant
- 422: montant invalide / order non payable (déjà payé, annulée)
- 423: device bloqué / IP interdite
- Audit:
    - Loguer: capture, payout/payin, open/close session, réimpressions
    - Spatie Activitylog avec subject = order/payment/device/member

Rapports V1 (minimal)
- Ventes du jour (POS): total espèces encaissées, nb tickets, panier moyen
- Mouvement caisse: export CSV (date, type, amount, member, reason)
- Détail par membre (si session ouverte): nb ventes, montant encaissé

Intégration PayPart (V1.1)
- Abstraction Payment Provider
    - PaymentIntent: id, provider: 'paypart', amount_cents, currency, status: 'requires_action'|'succeeded'|'failed', provider_ref, client_secret?, return_url
    - Méthodes serveur:
        - POST /v1/orders/{id}/payments/intents { provider: 'paypart', amount_cents }
        - Server crée intent via SDK/API PayPart; retourne client params
    - Client:
        - redirige/ouvre flow PayPart; récupère résultat
    - Webhooks:
        - /v1/payments/webhooks/paypart — vérifie signature, met à jour PaymentIntent + crée Payment(server-side)
    - États & idempotence:
        - lier PaymentIntent à order_id et idempotency_key
- Offline:
    - Paiement carte nécessite online; pas de capture offline
- Remboursements (V1.1):
    - POST /v1/payments/{id}/refunds { amount_cents? }
    - Émettre refund via provider; créer Payment (status 'refunded') + CashMovement si remboursement cash

Design UI/UX (reco)
- POS:
    - Écran encaissement: saisie montant donné, affichage rendu
    - Alertes idempotence (reçus doublons) → “paiement déjà capturé”
    - Indicateur session ouverte + raccourcis tiroir (open, payout, payin)
- Kiosk:
    - Si “payer à la caisse”: texte clair + numéro commande; impression automatique
- Back Office:
    - Journaux caisse; exports CSV; filtres par session/membre

OpenAPI (à documenter plus tard)
- POST /v1/orders/{id}/payments (payment.capture cash) — Idempotency-Key obligatoire
- POST /v1/drawer/{action} (open|payout|payin) — abilities drawer.*
- POST /v1/sessions/open|close — abilities session.open/close
- GET /v1/reports/sales?date=today — abilities sales.read
- V1.1:
    - POST /v1/orders/{id}/payments/intents (PayPart)
    - POST /v1/payments/webhooks/paypart
    - POST /v1/payments/{id}/refunds

Tests & cas limites
- Double-clic capture: même Idempotency-Key → 200 idempotent
- Rendu monnaie négatif (montant insuffisant) → 422
- Session close sans session open → 409
- Offline → tickets imprimés; re-sync → éviter double capture (idempotence)

Roadmap
- V1:
    - Cash + tiroir + sessions simples + tickets + journaux
    - Idempotence complète sur payments
- V1.1:
    - PayPart end-to-end, webhooks signés
    - Remboursements partiels
    - Rapport Z / Export fiscal
    - Contrôles & alertes (écarts de caisse, seuils approbations)
    - Multi-payments (split cash+carte)
