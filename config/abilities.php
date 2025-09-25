<?php

use App\Enum\StoreMemberRole;

return [
    /*
    |--------------------------------------------------------------------------
    | Versionnage & méta
    |--------------------------------------------------------------------------
    */
    'version' => 1,

    /*
    |--------------------------------------------------------------------------
    | Abilities unitaires (source de vérité)
    | - Clé => slug normalisé
    | - Valeur => description (utile pour back-office / front)
    |--------------------------------------------------------------------------
    */
    'abilities' => [

        // Store
        'store.read'      => 'Lire la boutique',
        'store.update'    => 'Mettre à jour la boutique',
        'store.delete'    => 'Supprimer la boutique',

        // Members
        'members.read'    => 'Lister/consulter les membres',
        'members.update'  => 'Modifier un membre',
        'members.create'  => 'Créer un membre',
        'members.delete'  => 'Supprimer un membre',
        'members.auth'    => 'Se connecter (POS/App)',
        'members.logout'  => 'Se déconnecter',

        // Orders
        'order.read'      => 'Lire commandes',
        'order.create'    => 'Créer commande',
        'order.update'    => 'Mettre à jour commande',
        'order.refund'    => 'Rembourser commande',
        'order.delete'    => 'Supprimer commande',
        'order.void'      => 'Annuler ticket/ligne',
        'order.discount'  => 'Appliquer remises',
        'order.reprint'   => 'Réimprimer ticket',
        'order.status.set'=> 'Changer statut commande',

        // Payments / tips
        'payment.capture' => 'Encaisser un paiement',
        'payment.refund'  => 'Rembourser un paiement',
        'tips.add'        => 'Ajouter un pourboire',

        // Menu
        'menu.read'       => 'Lire menu',
        'menu.update'     => 'Modifier menu',
        'menu.create'     => 'Créer entrée de menu',
        'menu.delete'     => 'Supprimer entrée de menu',
        'menu.publish'    => 'Publier menu',
        'menu.sync'       => 'Synchroniser menu vers devices',

        // Item
        'item.read'       => 'Lire items',
        'item.create'     => 'Créer item',
        'item.update'     => 'Modifier item',
        'item.delete'     => 'Supprimer item',

        // Planning
        'planning.read'   => 'Lire planning',
        'planning.create' => 'Créer planning',
        'planning.update' => 'Modifier planning',
        'planning.delete' => 'Supprimer planning',

        // Sales
        'sales.read'      => 'Lire ventes',
        'sales.update'    => 'Mettre à jour ventes',
        'sales.archive'   => 'Archiver ventes',
        'sales.delete'    => 'Supprimer ventes',

        // Devices (générique)
        'devices.read'    => 'Lire périphériques',
        'devices.update'  => 'Modifier périphériques',

        // Customer & RGPD
        'customer.read'        => 'Lire clients',
        'customer.create'      => 'Créer client',
        'customer.update'      => 'Modifier client',
        'customer.delete'      => 'Supprimer client',
        'customer.export'      => 'Exporter données client (RGPD)',
        'customer.anonymize'   => 'Anonymiser client (RGPD)',

        // Tickets
        'ticket.create'   => 'Créer ticket',

        // Sessions & tiroir
        'session.open'    => 'Ouvrir session',
        'session.close'   => 'Fermer session',
        'drawer.open'     => 'Ouvrir tiroir',
        'drawer.payout'   => 'Sortie de caisse',
        'drawer.payin'    => 'Entrée de caisse',
        'fiscal.z-export' => 'Export fiscal Z',

        // Kiosk / Wifi Order
        'kiosk.config.read'   => 'Lire config kiosks',
        'kiosk.config.update' => 'Modifier config kiosks',
        'wifi.order.read'     => 'Lire commandes WiFi',
        'wifi.order.update'   => 'Modifier commandes WiFi',

        // KDS
        'kds.read'        => 'Lire KDS',
        'kds.update'      => 'Mettre à jour bons KDS',
        'kds.route.rules' => 'Routage KDS',

        // Imprimantes & devices
        'printer.read'      => 'Lire imprimantes',
        'printer.update'    => 'Modifier imprimantes',
        'device.activate'   => 'Activer device',
        'device.token.issue'=> 'Générer token device',
        'device.sync'       => 'Synchroniser device',

        // Stocks / achats
        'stock.read'     => 'Lire stock',
        'stock.update'   => 'Modifier stock',
        'purchase.read'  => 'Lire achats',
        'purchase.update'=> 'Modifier achats',

        // Fidélité / marketing
        'loyalty.read'   => 'Lire fidélité',
        'loyalty.update' => 'Modifier fidélité',
        'coupon.apply'   => 'Appliquer coupon',
        'voucher.redeem' => 'Utiliser bon',

        // Reporting / audit / exports
        'analytics.read' => 'Lire analytics',
        'exports.create' => 'Créer exports',
        'audit.read'     => 'Lire audit',

        // Intégrations livraison
        'delivery.read'  => 'Lire intégrations livraison',
        'delivery.update'=> 'Modifier intégrations livraison',

        // Taxes
        'tax.read'       => 'Lire taxes',
        'tax.update'     => 'Modifier taxes',

        // Catégories
        'category.create'   => 'Créer catégorie',
        'categories.read'   => 'Lire catégories',
        'category.update'   => 'Modifier catégorie',
        'category.delete'   => 'Supprimer catégorie',
        'category.reorder'  => 'Réordonner catégories',
        'category.activate' => 'Activer/désactiver catégorie',

        'price.override' => 'Override prix exceptionnel',

        //Ingrédients
        'ingredient.read'   => 'Lire ingrédients',
        'ingredient.create' => 'Créer ingrédient',
        'ingredient.update' => 'Modifier ingrédient',
        'ingredient.delete' => 'Supprimer ingrédient',

        //Options
        'option.create'     => "Créer une option",
        "option.read"       => "Lire une option",
        "option.update"     => "Mettre à jour une option",
        "option.delete"     => "Supprimer une option",

        //Option Lists
        'option_list.create'    => "Créer une liste d'options",
        'option_list.read'      => "Lire une liste d'options",
        'option_list.update'    => "Mettre à jour une liste d'options",
        'option_list.delete'    => "Supprimer une liste d'options",
    ],

    /*
    |--------------------------------------------------------------------------
    | Groupes (factorisation & lisibilité)
    |--------------------------------------------------------------------------
    */
    'groups' => [

        'store.manage' => [
            'store.read','store.update','store.delete',
        ],

        'members.manage' => [
            'members.read','members.create','members.update','members.delete','members.auth','members.logout',
        ],

        'orders.base' => [
            'order.read','order.create','order.update',
        ],
        'orders.extended' => [
            '@orders.base','order.refund','order.delete','order.void','order.discount','order.reprint','order.status.set',
        ],

        'payments' => [
            'payment.capture','payment.refund','tips.add',
        ],

        'menu.full' => [
            'menu.read','menu.update','menu.create','menu.delete','menu.publish','menu.sync',
            'item.read','item.create','item.update','item.delete',
            'categories.read','category.create','category.update','category.delete','category.reorder','category.activate',
        ],

        'planning.full' => ['planning.read','planning.create','planning.update','planning.delete'],

        'sales.full' => ['sales.read','sales.archive','sales.delete','sales.update'],

        'devices.full' => [
            'devices.read','devices.update',
            'printer.read','printer.update',
            'device.activate','device.token.issue','device.sync',
            'kiosk.config.read','kiosk.config.update','wifi.order.read','wifi.order.update',
            'kds.read','kds.update','kds.route.rules',
        ],

        'stock.full' => ['stock.read','stock.update','purchase.read','purchase.update'],

        'loyalty.full' => ['loyalty.read','loyalty.update','coupon.apply','voucher.redeem'],

        'reporting.full' => ['analytics.read','exports.create','audit.read'],

        'rgpd' => ['customer.export','customer.anonymize'],

        'delivery.full' => ['delivery.read','delivery.update'],

        'tax.full' => ['tax.read','tax.update'],

        'session.cash' => [
            'session.open','session.close',
            'drawer.open','drawer.payout','drawer.payin',
            'fiscal.z-export',
        ],

        'ticket' => ['ticket.create'],

        'price.runtime' => ['price.override'],

        'item.manage' => [
            'item.read','item.create','item.update','item.delete',
        ],

        'ingredient.manage' => [
            'ingredient.read','ingredient.create','ingredient.update','ingredient.delete',
        ],

        'option.manage' => [
            'option.create', 'option.update', 'option.read', 'option.delete'
        ],

        'option_list.manage' => [
            'option_list.create', 'option_list.update', 'option_list.read', 'option_list.delete'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rôles -> ensembles d’abilities (via groupes + abilities)
    |--------------------------------------------------------------------------
    */
    'roles' => [
        StoreMemberRole::Owner->value => [
            '@store.manage','@members.manage','@orders.extended','@payments','@menu.full','@planning.full',
            '@sales.full','@devices.full','@stock.full','@loyalty.full','@reporting.full','@rgpd',
            '@delivery.full','@tax.full','@session.cash','@ticket','@price.runtime',
            'customer.read','customer.create','customer.update','customer.delete',
            '@item.manage', '@ingredient.manage', '@option.manage', '@option_list.manage'
        ],

        StoreMemberRole::Manager->value => [
            'store.read','store.update',
            '@members.manage',
            '@orders.extended','@payments',
            '@menu.full','@planning.full',
            '@sales.full',
            'devices.read','devices.update','printer.read','printer.update','device.sync',
            'stock.read','stock.update','purchase.read',
            '@loyalty.full','@reporting.full',
            '@delivery.full',
            'customer.read','customer.create','customer.update','customer.delete',
            '@session.cash','@ticket','@price.runtime',
            'members.auth','members.logout',
            'tax.read','tax.update',
            '@item.manage', '@ingredient.manage', '@option.manage', '@option_list.manage'
        ],

        StoreMemberRole::Cashier->value => [
            'store.read',
            '@orders.base','order.status.set','order.void','order.discount','order.reprint',
            'payment.capture','tips.add',
            'menu.read','item.read',
            'devices.read','printer.read','device.sync',
            'customer.read','customer.create','customer.update','customer.delete',
            '@session.cash','@ticket',
            'kds.read',
            'coupon.apply','voucher.redeem',
            'delivery.read',
            'members.auth','members.logout',
            'categories.read',
            'price.override',
            'ingredient.read',
            'option.read',
            'option_list.read'
        ],

        StoreMemberRole::Kitchen->value => [
            'store.read','order.read','menu.read','item.read',
            'devices.read','ticket.create','kds.read','kds.update','order.status.set','printer.read','device.sync',
            'members.auth','members.logout','categories.read',
            'ingredient.read',
            'option.read',
            'option_list.read',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exposition API (endpoints “front-friendly”)
    |--------------------------------------------------------------------------
    */
    'expose' => [
        'me_abilities_route' => '/api/v1/me/abilities',
        'cache' => [
            'enabled' => true,
            'key'     => 'abilities.registry.v1',
            'ttl'     => 3600, // 1h
        ],
    ],
];
