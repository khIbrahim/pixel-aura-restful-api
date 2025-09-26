# Catalog — Roadmap & Extensions (V1 ➜ V2+)

Objectif
- Lister les extensions prévues au-delà de la V1 “qui marche”.
- Cadrer le modèle de données extensible et les impacts API, perfs et UX.
- Préparer l’intégration avec Inventories, Comptabilité, Planning, Store Members.

Rappel V1 (opérationnel minimal)
- Entités: Category, Item, ItemVariant, OptionList, Option, Ingredient, Tax, Media.
- Conventions: SKU (identifiant public store), slugs pour catégories, is_active, menu_version, ETag/If-None-Match.
- Médias: main_image, gallery + conversions (thumbnail 300x300, banner 1200x600, icon 64x64).

Axes d’extension (Backlog priorisé) pour une v1.1 puis V2+
1) Pricing & Règles de prix
- Pricing par créneau (happy hours), calendrier (jours fériés), profils client (loyalty tiers).
- Remises: ligne, ticket, coupons (code unique/multi), règles de cumul, seuils (montant/quantité).
- Frais/charges: frais service, livraison, emballage; surcharge % ou fixe.

2) Menus & Compositions
- Menus/Combos/Bundles: “Menu Burger” avec choix de boisson/side (règles min/max).
- Sections & affichage: “Best sellers”, “Nouveautés”, “Kids”.
- Disponibilité par heure/jour (schedule par item/category/menu).
- Disponibilité par canal (POS/Kiosk/Website), rupture temporaire (86’d).

3) Taxes & Juridique
- Multi-taux par ligne (ex: TVA boisson vs alimentaire), règles pays/région.
- Mécanismes d’arrondi; facturation pro (TVA intracom), séries de factures.
- Export fiscal (Z) déjà prévu côté abilities (fiscal.z-export).

4) Inventaires & Recettes
- Recettes/BOM (Bill of Materials) par Item/Variant (liste d’Ingredients avec quantités et unités).
- Mouvements de stock (entrées, sorties, ajustements), unités de mesure, conversions.
- Réservations de stock à la commande; décrément au “capture” paiement ou au “ready”.
- Coûts matière (COGS) et marge; valorisation (FIFO/LIFO/standard) V2.

6) Comptabilité & Mapping
- Plans de comptes (GL), mapping produit->compte, TVA->code TVA, méthodes de reconnaissance revenus.
- Exports comptables (CSV/XLSX/Journal), intégrations ERP (Roadmap).
- Périodes & clôtures, rapports de ventes (journal caisse), remises, annulations, avoirs.

7) SEO & i18n
- i18n sur name/description (fr, ar, en…), alias multi-lang.
- Slug i18n (par canal), meta SEO pour le site.
- Ordres d’affichage par locale.

8) Recherche & UX
- Tags/Keywords; MeiliSearch pour “search-as-you-type”.
- Facettes (catégories, allergènes), suggestions.

9) Gouvernance & Workflow
- Brouillons (draft), publication (publish), versioning de menu; audit (Spatie).
- Verrous d’édition (optimistic locking, updated_at + If-Unmodified-Since).

10) Multi-store & Organisation (post-V1)
- Organisation → plusieurs stores; partage/synchronisation de catalogues (templates).
- Overrides par store (prix, disponibilité, médias).

Modèle de données — Extensions (proposition champs)
- Item
    - channel_visibility: { pos: bool, kiosk: bool, web: bool }
    - availability: { schedule_id?: int, out_of_stock_until?: datetime }
    - pricing: { policy: 'base|per_channel|per_schedule', price_books?: [id] }
    - nutrition?: { kcal:int, protein_g:int, carbs_g:int, fat_g:int, allergens:[string], labels:[string] }
    - accounting?: { revenue_account:string, tax_code:string }
    - merchandising?: { badges:[string], rank:int }
- ItemVariant
    - price_mode: 'delta|absolute', price_cents?: int, price_delta_cents?: int
    - barcode?, sku?, accounting?, media collections (optionnel V1.1)
- OptionList
    - channel_visibility, schedule_id?, required_by_default:bool
- Option
    - price_mode, price_cents/price_delta_cents, allergens?, labels?
- Ingredient
    - uom:'g|ml|piece', cost_per_uom_cents, supplier_id?, sku_supplier?, reorder_level, reorder_qty
- Tax
    - rate_percent, inclusive:bool (override store), country/region?, external_code?

Planning & Schedules
- Entity: Schedule
    - id, name, timezone, rules: [{ day_of_week, start_time, end_time }]
- Attachments
    - Category.schedule_id?, Item.schedule_id?, OptionList.schedule_id?
- Évaluation
    - Côté API: utilitaire “isAvailableAt(datetime)” ➜ Kiosk/POS affichage.

Événements à émettre (domain events)
- CatalogUpdated (avec menu_version++ et scopes affectés: {store_id, entities:[…]})
- ItemOutOfStock / ItemBackInStock
- PriceBookPublished / ScheduleChanged
- MediaUpdated (pour invalider caches front)

API & perfs
- Endpoints “compacts” par canal (ex: GET /v1/catalog/compact?channel=kiosk&lang=fr)
    - Filtrage: is_active, availability now, channel_visibility.
    - ETag basé sur menu_version + hash du payload compact.
- Pagination & tri cohérents (page/limit, position, name).
- MeiliSearch: index “items” avec champs cherchables (name, tags, categories).

Exemples compacts (Kiosk, extension)
```json
{
  "menu_version": 4,
  "categories": [
    {"id":12,"name":"Burgers","slug":"burgers","position":1,"is_active":true,"thumbnail":"https://.../cat-12-thumb.webp"}
  ],
  "items": [
    {
      "id":345,"category_id":12,"sku":"BRG-CLASSIC","name":"Classic Burger","is_active":true,
      "base_price_cents":12000,"currency":"DZD",
      "visibility":{"pos":true,"kiosk":true,"web":true},
      "availability":{"is_available_now":true},
      "variants":[{"id":1,"name":"Single","price_delta_cents":0},{"id":2,"name":"Double","price_delta_cents":3000}],
      "option_lists":[{"id":9,"name":"Extras","type":"multiple","min_select":0,"max_select":3}],
      "tax_id":2,
      "thumbnail":"https://.../item-345-thumb.webp"
    }
  ]
}
```

Tâches V1 (do now)
- Finaliser Catalog V1 conforme docs/03-domain-model.md.
- Uniformiser images: 2 collections + 3 conversions; routes Media génériques OK.
- Incrément menu_version transactionnel sur changements catalogues.
- Endpoints compacts pour Kiosk avec ETag.

Tâches V1.1 (next)
- PriceBooks par canal + schedules; OptionList avancées (min/max par canal).
- Recettes (BOM) + décrément stock basique par vente; mouvements manuels.
- Draft/Publish & audit des modifications.
- Allergènes/Nutrition de base; recherche MeiliSearch.

Risques & garde-fous
- Complexité pricing: commencer simple (delta variant, delta option) puis étendre.
- Performance payload compact: viser <1–2 MB compressé; pagination sectional.
- Conflits publication: “last write wins” + audit + diff.

Dépendances transverses
- Inventories: nécessite BOM et hooks sur statut commande.
- Comptabilité: mapping GL par item/tax et export batch.
- Planning: réutilise Schedule sur plusieurs entités.

Notes (prises de la discussion)
- V1 priorise fonctionnement simple; extensions prévues “tarpin” pour V2+.
- Store Members: gestion possible dès maintenant (fichier dédié à venir).
- OpenAPI: sera traité après stabilisation de ces docs.
