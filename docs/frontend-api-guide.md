# Guide API pour Développeurs Front-End

## Vue d'ensemble

Cette API REST est conçue pour un système POS (Point of Sale) centré sur l'écosystème restaurant. Elle supporte les bornes self-ordering, caisses enregistreuses, écrans de cuisine, et applications back-office.

## 🎯 LE CATALOGUE : PIÈCE MAÎTRESSE DU SYSTÈME

**Le catalogue est le cœur absolu de votre POS !** C'est la représentation optimisée et cachée de votre menu complet qui alimente TOUS vos écrans :

- 🏪 **Bornes self-ordering** : Affichage du menu client
- 💰 **Caisses POS** : Sélection produits pour commandes
- 👨‍💼 **Back-office** : Gestion du menu et des prix
- 📱 **Apps mobiles** : Commande en ligne

### Pourquoi le catalogue est critique :
1. **Performance** : Cache Redis optimisé avec ETag pour éviter les rechargements inutiles
2. **Cohérence** : Une seule source de vérité pour tous les channels (kiosk, pos, web)
3. **Versioning** : Système de versions (`menu_version`) pour synchronisation
4. **Temps réel** : Mise à jour automatique via WebSockets quand le menu change

**⚠️ RÈGLE D'OR** : Toujours commencer par charger le catalogue avant d'afficher un menu !

## 🚀 Configuration Rapide pour le Développement

### Option 1: Utiliser le Mock Server (Recommandé pour démarrer)

1. **Installation de Prism (Mock Server)**
   ```bash
   # Installation globale
   npm install -g @stoplight/prism-cli
   
   # OU installation locale dans votre projet
   npm install -D @stoplight/prism-cli
   ```

2. **Lancer le mock server**
   ```bash
   # Depuis la racine du projet (là où se trouve openapi/)
   prism mock openapi/main.openapi.yaml --port 4010
   
   # Le serveur mock sera disponible sur http://localhost:4010
   ```

3. **Configuration de votre application front-end**
   ```env
   # Dans votre fichier .env
   VITE_API_URL=http://localhost:4010/api/v1
   VITE_API_BASE_URL=http://localhost:4010
   ```

### Option 2: Utiliser l'API Réelle

```env
# Configuration pour l'API de développement
VITE_API_URL=http://localhost:8000/api/v1
VITE_API_BASE_URL=http://localhost:8000

# Configuration pour l'API de production (si disponible)
VITE_API_URL=https://api.pixel-pos.com/v1
VITE_API_BASE_URL=https://api.pixel-pos.com
```

## 🔐 Authentification

### Token Bearer

Toutes les requêtes nécessitent un token d'authentification dans l'en-tête :

```javascript
const headers = {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

### Obtenir un Token

Pour les tests avec le mock server, vous pouvez utiliser un token factice :

```javascript
const MOCK_TOKEN = 'mock-token-for-development-only'
```

Pour l'API réelle, vous devrez implémenter l'authentification device/store member.

## 📋 Endpoints Complets par Module

### 🎯 1. CATALOGUE (Pièce Maîtresse)

```javascript
// GET /api/v1/catalog/compact - LE PLUS IMPORTANT !
const getCatalog = async (channel = 'kiosk', useCache = true) => {
  const headers = {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
  
  // OBLIGATOIRE : Gestion ETag pour cache
  const cachedETag = localStorage.getItem('catalog_etag')
  if (useCache && cachedETag) {
    headers['If-None-Match'] = cachedETag
  }
  
  const response = await fetch(`${API_URL}/catalog/compact?channel=${channel}`, { headers })
  
  // 304 = Catalogue non modifié, utiliser le cache local
  if (response.status === 304) {
    console.log('✅ Catalogue à jour, utilisation du cache')
    return JSON.parse(localStorage.getItem('catalog_data'))
  }
  
  const catalog = await response.json()
  
  // Sauvegarder l'ETag et les données
  localStorage.setItem('catalog_etag', response.headers.get('ETag'))
  localStorage.setItem('catalog_data', JSON.stringify(catalog))
  
  console.log(`📋 Catalogue chargé - Version: ${response.headers.get('X-Menu-Version')}`)
  return catalog
}

// POST /api/v1/catalog/refresh - Force la recompilation
const refreshCatalog = async (channel = 'all') => {
  const response = await fetch(`${API_URL}/catalog/refresh?channel=${channel}`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  })
  
  if (!response.ok) {
    const error = await response.json()
    throw new Error(`Erreur compilation catalogue: ${error.error} - ${error.message}`)
  }
  
  return response.json()
}
```

### 🗂️ 2. CATÉGORIES (Organisation du Menu)

```javascript
// GET /api/v1/categories - Liste des catégories
const getCategories = async (filters = {}) => {
  const params = new URLSearchParams(filters)
  const response = await fetch(`${API_URL}/categories?${params}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  })
  return response.json()
}

// POST /api/v1/categories - Créer une catégorie
const createCategory = async (categoryData) => {
  const response = await fetch(`${API_URL}/categories`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(categoryData)
  })
  return response.json()
}

// PATCH /api/v1/categories/reorder - Réorganiser les catégories
const reorderCategories = async (items) => {
  const response = await fetch(`${API_URL}/categories/reorder`, {
    method: 'PATCH',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ items })
  })
  return response.json()
}

// Exemple de réordonnancement
const newOrder = [
  { id: 3, position: 1 }, // Desserts en premier
  { id: 1, position: 2 }, // Plats en second
  { id: 2, position: 3 }  // Boissons en dernier
]
await reorderCategories(newOrder)
```

### 🍕 3. ITEMS (Produits/Plats) - CRUD Complet

```javascript
// GET /api/v1/items - Liste paginée des items
const getItems = async (page = 1, perPage = 25, filters = {}) => {
  const params = new URLSearchParams({
    page,
    per_page: perPage,
    ...filters
  })
  
  const response = await fetch(`${API_URL}/items?${params}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  })
  return response.json()
}

// GET /api/v1/items/{item} - Détails d'un item avec relations
const getItem = async (itemId, withRelations = true) => {
  const params = withRelations ? '?with=category,variants,ingredients,options,media' : ''
  const response = await fetch(`${API_URL}/items/${itemId}${params}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  })
  return response.json()
}

// POST /api/v1/items - Créer un item complet
const createCompleteItem = async (itemData) => {
  const response = await fetch(`${API_URL}/items`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(itemData)
  })
  return response.json()
}

// Exemple d'item complet avec toutes ses relations
const pizzaItem = {
  name: "Pizza 4 Fromages",
  description: "Mozzarella, gorgonzola, parmesan, chèvre",
  category_id: 1,
  price: 14.50,
  sku: "PIZZA-4F",
  position: 5,
  is_active: true,
  
  // Variants de taille
  variants: [
    { name: "Moyenne", price: 14.50, sku: "PIZZA-4F-M" },
    { name: "Grande", price: 18.50, sku: "PIZZA-4F-L" },
    { name: "Familiale", price: 22.50, sku: "PIZZA-4F-XL" }
  ],
  
  // Ingrédients de base
  ingredients: [
    { ingredient_id: 1, name: "Mozzarella", is_mandatory: true },
    { ingredient_id: 5, name: "Gorgonzola", is_mandatory: true },
    { ingredient_id: 8, name: "Parmesan", is_mandatory: true },
    { ingredient_id: 12, name: "Chèvre", is_mandatory: true }
  ],
  
  // Options disponibles
  options: [
    { option_id: 3, name: "Supplément Olives", price_cents: 150 },
    { option_id: 7, name: "Pâte Fine", price_cents: 0 },
    { option_id: 15, name: "Supplément Roquette", price_cents: 200 }
  ]
}
```

### 🎛️ 4. OPTIONS (Personnalisations)

```javascript
// GET /api/v1/options - Liste avec filtres avancés
const getOptions = async (filters = {}) => {
  const params = new URLSearchParams({
    per_page: 50,
    search: filters.search || '',
    price_cents: filters.price_cents,
    price_cents_operator: filters.price_operator || '>=',
    is_active: filters.is_active !== undefined ? filters.is_active : true,
    ...filters
  })
  
  const response = await fetch(`${API_URL}/options?${params}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  })
  return response.json()
}

// POST /api/v1/options - Créer une option
const createOption = async (optionData) => {
  const response = await fetch(`${API_URL}/options`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(optionData)
  })
  return response.json()
}

// Exemples d'options par catégorie
const optionExamples = {
  sauce: {
    name: "Sauce Barbecue",
    description: "Sauce barbecue fumée maison",
    price_cents: 50, // 0.50€
    is_active: true
  },
  extra: {
    name: "Supplément Bacon",
    description: "Tranches de bacon croustillant",
    price_cents: 250, // 2.50€
    is_active: true
  },
  modification: {
    name: "Sans Oignon",
    description: "Retirer les oignons du plat",
    price_cents: 0, // Gratuit
    is_active: true
  }
}
```

### 🔗 5. ATTACHEMENTS D'ITEMS (Relations)

```javascript
// POST /api/v1/items/{item}/ingredients - Attacher des ingrédients
const attachIngredients = async (itemId, ingredients) => {
  const response = await fetch(`${API_URL}/items/${itemId}/ingredients`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ ingredients })
  })
  return response.json()
}

// POST /api/v1/items/{item}/options - Attacher des options
const attachOptions = async (itemId, options) => {
  const response = await fetch(`${API_URL}/items/${itemId}/options`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ options })
  })
  return response.json()
}

// POST /api/v1/items/{item}/option-lists - Attacher des groupes d'options
const attachOptionLists = async (itemId, optionLists) => {
  const response = await fetch(`${API_URL}/items/${itemId}/option-lists`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ option_lists: optionLists })
  })
  return response.json()
}

// Exemple d'attachement d'options avec personnalisation
const optionsToAttach = [
  {
    id: 1, // Option existante
    name: "Sauce Ketchup", // Nom personnalisé
    price_cents: 0, // Prix personnalisé (gratuit)
    is_active: true
  },
  {
    id: 5, // Option "Supplément Fromage"
    // Utilise les valeurs par défaut de l'option
  },
  {
    id: 8,
    name: "Extra Bacon", // Nom personnalisé pour cet item
    price_cents: 300 // Prix spécial pour cet item
  }
]
```

### 🖼️ 6. MÉDIAS (Images) - Tous Types

```javascript
// Upload image principale pour n'importe quel modèle
const uploadMainImage = async (type, modelId, imageFile) => {
  const formData = new FormData()
  formData.append('image', imageFile)
  
  const response = await fetch(`${API_URL}/${type}/${modelId}/media/main`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData
  })
  return response.json()
}

// Types supportés selon votre roadmap
const MEDIA_TYPES = [
  'items',        // Produits/plats
  'categories',   // Catégories de menu
  'options',      // Options/modificateurs
  'ingredients',  // Ingrédients
  'item-variants',// Variants d'items
  'store-member', // Photos de profil employés
  'store',        // Logo du magasin
  'device'        // Images des terminaux
]

// Upload d'image via URL (utile pour l'import)
const uploadImageFromUrl = async (type, modelId, imageUrl) => {
  const response = await fetch(`${API_URL}/${type}/${modelId}/media/main`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ image_url: imageUrl })
  })
  return response.json()
}

// Galerie d'images (pour items avec plusieurs photos)
const getImageGallery = async (type, modelId) => {
  const response = await fetch(`${API_URL}/${type}/${modelId}/media/gallery`, {
    headers: { 'Authorization': `Bearer ${token}` }
  })
  return response.json()
}

// Ajouter à la galerie
const addToGallery = async (type, modelId, imageFile) => {
  const formData = new FormData()
  formData.append('image', imageFile)
  
  const response = await fetch(`${API_URL}/${type}/${modelId}/media/gallery`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData
  })
  return response.json()
}
```

### 👥 7. STORE MEMBERS (Gestion Employés)

```javascript
// GET /api/v1/store-members - Liste des employés du magasin
const getStoreMembers = async (filters = {}) => {
  const params = new URLSearchParams({
    per_page: 50,
    role: filters.role, // owner, manager, cashier, kitchen, employee
    is_active: filters.is_active,
    search: filters.search,
    ...filters
  })
  
  const response = await fetch(`${API_URL}/store-members?${params}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  })
  return response.json()
}

// POST /api/v1/store-members/authenticate - Authentification par PIN
const authenticateStoreMember = async (memberCode, pin) => {
  const response = await fetch(`${API_URL}/store-members/authenticate`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      code: memberCode, // Ex: "EMP-001"
      pin: pin         // Code PIN à 4-8 chiffres
    })
  })
  return response.json()
}

// POST /api/v1/store-members/logout - Déconnexion
const logoutStoreMember = async () => {
  const response = await fetch(`${API_URL}/store-members/logout`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` }
  })
  return response.json()
}
```

## 🎯 Patterns d'Utilisation par Interface

### Interface Borne Self-Ordering (Kiosk)

```javascript
class KioskManager {
  constructor(apiBaseUrl, deviceToken) {
    this.api = apiBaseUrl
    this.token = deviceToken
    this.catalog = null
    this.currentOrder = []
  }
  
  // 1. TOUJOURS commencer par charger le catalogue
  async initialize() {
    try {
      console.log('🚀 Initialisation de la borne...')
      
      // Chargement du catalogue (PIÈCE MAÎTRESSE)
      this.catalog = await this.loadCatalog()
      
      // Vérification des infos device
      const deviceInfo = await this.getDeviceInfo()
      
      console.log('✅ Borne prête - Menu version:', this.catalog.version)
      return true
    } catch (error) {
      console.error('❌ Erreur initialisation borne:', error)
      return false
    }
  }
  
  async loadCatalog() {
    const response = await fetch(`${this.api}/catalog/compact?channel=kiosk`, {
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'If-None-Match': localStorage.getItem('catalog_etag') || ''
      }
    })
    
    if (response.status === 304) {
      return JSON.parse(localStorage.getItem('catalog_data'))
    }
    
    const catalog = await response.json()
    localStorage.setItem('catalog_etag', response.headers.get('ETag'))
    localStorage.setItem('catalog_data', JSON.stringify(catalog))
    
    return catalog
  }
  
  // Construire un item de commande avec options sélectionnées
  buildOrderItem(item, selectedVariant, selectedOptions = []) {
    const basePrice = selectedVariant ? selectedVariant.price : item.price
    const optionsPrice = selectedOptions.reduce((total, opt) => 
      total + (opt.price_cents / 100), 0
    )
    
    return {
      item_id: item.id,
      variant_id: selectedVariant?.id,
      quantity: 1,
      unit_price: basePrice + optionsPrice,
      selected_options: selectedOptions.map(opt => ({
        option_id: opt.id,
        name: opt.name,
        price_cents: opt.price_cents
      })),
      selected_ingredients: item.ingredients.filter(ing => ing.is_mandatory)
    }
  }
  
  // Interface de sélection d'options pour un item
  async showItemCustomization(item) {
    // Charger les options disponibles pour cet item
    const itemDetails = await fetch(`${this.api}/items/${item.id}?with=options,variants`, {
      headers: { 'Authorization': `Bearer ${this.token}` }
    }).then(r => r.json())
    
    return {
      variants: itemDetails.data.variants,
      options: itemDetails.data.options,
      baseItem: itemDetails.data
    }
  }
}
```

### Interface POS (Caisse)

```javascript
class POSManager {
  constructor(apiBaseUrl, deviceToken) {
    this.api = apiBaseUrl
    this.token = deviceToken
    this.currentMember = null
    this.currentOrder = []
  }
  
  // Connexion employé OBLIGATOIRE pour POS
  async loginMember(memberCode, pin) {
    try {
      const response = await fetch(`${this.api}/store-members/authenticate`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ code: memberCode, pin })
      })
      
      if (!response.ok) {
        const error = await response.json()
        throw new Error(error.message)
      }
      
      const result = await response.json()
      this.currentMember = result.data
      
      console.log(`✅ Employé connecté: ${this.currentMember.name} (${this.currentMember.role})`)
      
      // Charger le catalogue après connexion
      await this.loadCatalog()
      
      return result
    } catch (error) {
      console.error('❌ Erreur connexion employé:', error)
      throw error
    }
  }
  
  async loadCatalog() {
    // Même logique que la borne mais channel 'pos'
    const response = await fetch(`${this.api}/catalog/compact?channel=pos`, {
      headers: { 'Authorization': `Bearer ${this.token}` }
    })
    
    this.catalog = await response.json()
    return this.catalog
  }
  
  // Recherche rapide d'items (pour code-barres ou recherche)
  async searchItems(query) {
    const response = await fetch(`${this.api}/items?search=${encodeURIComponent(query)}&per_page=10`, {
      headers: { 'Authorization': `Bearer ${this.token}` }
    })
    
    return response.json()
  }
}
```

### Interface Back-Office (Gestion Menu)

```javascript
class BackOfficeManager {
  async createCompleteMenuItem() {
    try {
      // 1. Créer l'item de base
      const item = await this.createItem({
        name: "Nouveau Burger",
        category_id: 2,
        price: 8.90
      })
      
      // 2. Ajouter l'image principale
      await this.uploadMainImage('items', item.data.id, imageFile)
      
      // 3. Attacher des options
      await this.attachOptions(item.data.id, [
        { id: 1, name: "Sauce Ketchup", price_cents: 0 },
        { id: 5, name: "Supplément Fromage", price_cents: 150 }
      ])
      
      // 4. Attacher des ingrédients
      await this.attachIngredients(item.data.id, [
        { ingredient_id: 10, name: "Steak Haché", is_mandatory: true },
        { ingredient_id: 15, name: "Salade", is_mandatory: false }
      ])
      
      // 5. IMPORTANT: Rafraîchir le catalogue après modifications
      await fetch(`${this.api}/catalog/refresh`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${this.token}` }
      })
      
      console.log('✅ Item créé et catalogue mis à jour')
      
    } catch (error) {
      console.error('❌ Erreur création item:', error)
    }
  }
}
```

## 🚨 Règles Critiques selon votre Roadmap

### 1. Gestion du Cache Catalogue (OBLIGATOIRE)
```javascript
// TOUJOURS envoyer If-None-Match pour le catalogue
const headers = {
  'Authorization': `Bearer ${token}`,
  'If-None-Match': localStorage.getItem('catalog_etag') || ''
}

// Surveiller les changements de menu_version
if (newMenuVersion > currentMenuVersion) {
  await refreshCatalog()
  localStorage.setItem('menu_version', newMenuVersion)
}
```

### 2. Idempotence pour Orders/Payments
```javascript
// OBLIGATOIRE: Idempotency-Key pour éviter les doublons
const idempotencyKey = `order-${Date.now()}-${Math.random().toString(36)}`

const createOrder = async (orderData) => {
  const response = await fetch(`${API_URL}/orders`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Idempotency-Key': idempotencyKey // OBLIGATOIRE
    },
    body: JSON.stringify(orderData)
  })
  return response.json()
}
```

### 3. Types de Modèles pour Médias
```javascript
// Nommage EXACT selon votre roadmap
const MEDIA_TYPES = {
  CATEGORIES: 'categories',
  ITEMS: 'items', 
  OPTIONS: 'options',
  INGREDIENTS: 'ingredients',
  OPTION_LISTS: 'option-lists', // Avec tiret !
  ITEM_VARIANTS: 'item-variants' // Avec tiret !
}
```

### 4. Format Monétaire
```javascript
// TOUJOURS en centimes pour éviter les erreurs de précision
const price = {
  display: '12.50€',        // Pour affichage
  api: 1250,               // Pour l'API (en centimes)
  calculation: 1250 / 100  // Pour calculs
}
```

## 🔄 Workflow Complet

1. **Initialisation** : Charger le catalogue avec ETag
2. **Authentification** : Login store member si nécessaire
3. **Navigation** : Utiliser le catalogue pour afficher le menu
4. **Commandes** : Construire avec idempotency-key
5. **Médias** : Upload avec types exacts
6. **Mise à jour** : Rafraîchir catalogue après modifications

Cette documentation couvre maintenant TOUS vos endpoints selon votre roadmap ! Le catalogue est bien présenté comme la pièce maîtresse du système.
