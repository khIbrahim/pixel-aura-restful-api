<?php
namespace App\Constants\V1;

use App\Enum\StoreMemberRole;
use ReflectionClass;

final class StoreTokenAbilities
{
    public const string STORE_READ   = 'store:read';
    public const string STORE_UPDATE = 'store:update';
    public const string STORE_DELETE = 'store:delete';

    public const string MEMBERS_READ   = 'members:read';
    public const string MEMBERS_UPDATE = 'members:update';
    public const string MEMBERS_CREATE = 'members:create';
    public const string MEMBERS_DELETE = 'members:delete';
    public const string MEMBERS_AUTH   = 'members:auth';
    public const string MEMBERS_LOGOUT = 'members:logout';

    public const string ORDER_READ     = 'order:read';
    public const string ORDER_CREATE   = 'order:create';
    public const string ORDER_UPDATE   = 'order:update';
    public const string ORDER_REFUND   = 'order:refund';
    public const string ORDER_DELETE   = 'order:delete';

    public const string MENU_READ      = 'menu:read';
    public const string MENU_UPDATE    = 'menu:update';
    public const string MENU_CREATE    = 'menu:create';
    public const string MENU_DELETE    = 'menu:delete';

    public const string ITEM_READ      = 'item.read';
    public const string ITEM_CREATE    = 'item.create';
    public const string ITEM_UPDATE    = 'item.update';
    public const string ITEM_DELETE    = 'item:delete';

    public const string PLANNING_READ   = 'planning:read';
    public const string PLANNING_CREATE = 'planning:create';
    public const string PLANNING_UPDATE = 'planning:update';
    public const string PLANNING_DELETE = 'planning:delete';

    public const string SALES_READ        = 'sales:read';
    public const string SALES_UPDATE      = 'sales:update';
    public const string SALES_ARCHIVE     = 'sales:archive';
    public const string SALES_DELETE      = 'sales:delete';

    public const string DEVICES_READ      = 'devices:read';
    public const string DEVICES_UPDATE    = 'devices:update';

    public const string CUSTOMER_READ     = 'customer:read';
    public const string CUSTOMER_CREATE   = 'customer:create';
    public const string CUSTOMER_UPDATE   = 'customer:update';
    public const string CUSTOMER_DELETE   = 'customer:delete';

    public const string TICKET_CREATE     = 'ticket:create';

    // --- AJOUTS RECOMMANDÉS ---

// Sessions & tiroir
    public const string SESSION_OPEN      = 'session:open';
    public const string SESSION_CLOSE     = 'session:close';
    public const string DRAWER_OPEN       = 'drawer:open';
    public const string DRAWER_PAYOUT     = 'drawer:payout';     // sorties de caisse
    public const string DRAWER_PAYIN      = 'drawer:payin';      // entrées de caisse
    public const string Z_REPORT_EXPORT   = 'fiscal:z-export';   // clôture Z / export fiscal

// Commandes (granularité opérationnelle POS/Kiosk)
    public const string ORDER_VOID        = 'order:void';        // annulation ticket/ligne
    public const string ORDER_DISCOUNT    = 'order:discount';    // remises
    public const string ORDER_REPRINT     = 'order:reprint';     // réimpression ticket
    public const string ORDER_STATUS_SET  = 'order:status:set';  // accept/in_prep/ready/served etc. (KDS/Wifi)

// Paiements
    public const string PAYMENT_CAPTURE   = 'payment:capture';
    public const string PAYMENT_REFUND    = 'payment:refund';    // parfois distinct de order:refund
    public const string TIPS_ADD          = 'tips:add';

// Menu & pricing runtime
    public const string PRICE_OVERRIDE    = 'price:override';    // override exceptionnel au POS
    public const string MENU_PUBLISH      = 'menu:publish';      // publication (kiosks/wifi)
    public const string MENU_SYNC         = 'menu:sync';         // pull/push configs vers devices

// Kiosk / Wifi Order (devices & flux)
    public const string KIOSK_CONFIG_READ   = 'kiosk:config:read';
    public const string KIOSK_CONFIG_UPDATE = 'kiosk:config:update';
    public const string WIFI_ORDER_READ     = 'wifi:order:read';
    public const string WIFI_ORDER_UPDATE   = 'wifi:order:update';

// Kitchen Display (KDS)
    public const string KDS_READ          = 'kds:read';
    public const string KDS_UPDATE        = 'kds:update';        // bump, ready, done
    public const string KDS_ROUTE_RULES   = 'kds:route:rules';   // routage postes/sections

// Périphériques & imprimantes
    public const string PRINTER_READ      = 'printer:read';
    public const string PRINTER_UPDATE    = 'printer:update';
    public const string DEVICE_ACTIVATE   = 'device:activate';   // appairage/activation
    public const string DEVICE_TOKEN_ISSUE= 'device:token:issue';
    public const string DEVICE_SYNC       = 'device:sync';       // pull config, health ping

// Stocks / back-office light
    public const string STOCK_READ        = 'stock:read';
    public const string STOCK_UPDATE      = 'stock:update';
    public const string PURCHASE_READ     = 'purchase:read';
    public const string PURCHASE_UPDATE   = 'purchase:update';

// Fidélité / marketing (YouFid & co)
    public const string LOYALTY_READ      = 'loyalty:read';
    public const string LOYALTY_UPDATE    = 'loyalty:update';
    public const string COUPON_APPLY      = 'coupon:apply';
    public const string VOUCHER_REDEEM    = 'voucher:redeem';

// Reporting / exports / audit
    public const string ANALYTICS_READ    = 'analytics:read';    // stats temps réel / dashboard
    public const string EXPORTS_CREATE    = 'exports:create';    // CSV/Excel/PDF
    public const string AUDIT_READ        = 'audit:read';

// RGPD / clients
    public const string CUSTOMER_EXPORT   = 'customer:export';   // portabilité
    public const string CUSTOMER_ANONYMIZE= 'customer:anonymize';// anonymisation RGPD

// Intégrations (livraison, etc.)
    public const string DELIVERY_READ     = 'delivery:read';
    public const string DELIVERY_UPDATE   = 'delivery:update';   // remettre une commande au bon état

// Réglages fiscaux & taxes (back-office)
    public const string TAX_READ          = 'tax:read';
    public const string TAX_UPDATE        = 'tax:update';

    public const string CATEGORY_CREATE = 'category.create';
    public const string CATEGORY_READ   = 'categories.read';
    public const string CATEGORY_UPDATE = 'category.update';
    public const string CATEGORY_DELETE = 'category.delete';
    public const string CATEGORY_REORDER = 'category.reorder';
    public const string CATEGORY_ACTIVATE = 'category.activate';
    public const string CREATE_INGREDIENT = 'ingredient.create';
    public const string INGREDIENT_READ = 'ingredient.read';
    public const string UPDATE_INGREDIENT = 'ingredient.update';
    public const string INGREDIENT_DELETE = 'ingredient.delete';

    // Options
    public const string OPTION_CREATE = 'option.create';
    public const string OPTION_READ   = 'option.read';
    public const string UPDATE_OPTION = 'option.update';
    public const string OPTION_DELETE = 'option.delete';

    //Options liste
    public const string OPTION_LIST_CREATE = 'option_list.create';
    public const string OPTION_LIST_READ   = 'option_list.read';
    public const string OPTION_LIST_UPDATE = 'option_list.update';
    public const string OPTION_LIST_DELETE = 'option_list.delete';

    // Medias
    public const string MEDIA_VIEW   = 'media.view';
    public const string MEDIA_UPLOAD = 'media.upload';
    public const string MEDIA_DELETE = 'media.delete';

    public static function getAbilitiesByRole(StoreMemberRole $role): array
    {
        return match ($role) {
            StoreMemberRole::Owner => [
                self::STORE_READ, self::STORE_UPDATE, self::STORE_DELETE,
                self::MEMBERS_READ, self::MEMBERS_CREATE, self::MEMBERS_UPDATE, self::MEMBERS_DELETE,
                self::ORDER_READ, self::ORDER_CREATE, self::ORDER_UPDATE, self::ORDER_REFUND, self::ORDER_DELETE,
                self::ITEM_READ, self::ITEM_CREATE, self::ITEM_UPDATE, self::ITEM_DELETE,
                self::MENU_READ, self::MENU_UPDATE, self::MENU_CREATE, self::MENU_DELETE,
                self::PLANNING_READ, self::PLANNING_CREATE, self::PLANNING_UPDATE, self::PLANNING_DELETE,
                self::SALES_READ, self::SALES_ARCHIVE, self::SALES_DELETE, self::SALES_UPDATE,
                self::DEVICES_READ, self::DEVICES_UPDATE,
                self::CUSTOMER_READ, self::CUSTOMER_CREATE, self::CUSTOMER_UPDATE, self::CUSTOMER_DELETE,
                self::TICKET_CREATE,

                self::SESSION_OPEN, self::SESSION_CLOSE,
                self::DRAWER_OPEN, self::DRAWER_PAYOUT, self::DRAWER_PAYIN,
                self::Z_REPORT_EXPORT,

                self::ORDER_VOID, self::ORDER_DISCOUNT, self::ORDER_REPRINT, self::ORDER_STATUS_SET,
                self::PAYMENT_CAPTURE, self::PAYMENT_REFUND, self::TIPS_ADD,

                self::PRICE_OVERRIDE, self::MENU_PUBLISH, self::MENU_SYNC,

                self::KIOSK_CONFIG_READ, self::KIOSK_CONFIG_UPDATE,
                self::WIFI_ORDER_READ, self::WIFI_ORDER_UPDATE,

                self::KDS_READ, self::KDS_UPDATE, self::KDS_ROUTE_RULES,

                self::PRINTER_READ, self::PRINTER_UPDATE,
                self::DEVICE_ACTIVATE, self::DEVICE_TOKEN_ISSUE, self::DEVICE_SYNC,

                self::STOCK_READ, self::STOCK_UPDATE, self::PURCHASE_READ, self::PURCHASE_UPDATE,

                self::LOYALTY_READ, self::LOYALTY_UPDATE, self::COUPON_APPLY, self::VOUCHER_REDEEM,

                self::ANALYTICS_READ, self::EXPORTS_CREATE, self::AUDIT_READ,

                self::CUSTOMER_EXPORT, self::CUSTOMER_ANONYMIZE,

                self::DELIVERY_READ, self::DELIVERY_UPDATE,

                self::TAX_READ, self::TAX_UPDATE,
                self::MEMBERS_LOGOUT, self::MEMBERS_AUTH,

                self::CATEGORY_READ, self::CATEGORY_CREATE, self::CATEGORY_UPDATE, self::CATEGORY_DELETE, self::CATEGORY_REORDER, self::CATEGORY_ACTIVATE,

                self::CREATE_INGREDIENT, self::INGREDIENT_READ, self::UPDATE_INGREDIENT, self::INGREDIENT_DELETE,
                self::OPTION_CREATE, self::OPTION_READ, self::UPDATE_OPTION, self::OPTION_DELETE
            ],
            StoreMemberRole::Manager => [
                self::STORE_READ, self::STORE_UPDATE,
                self::MEMBERS_READ, self::MEMBERS_CREATE, self::MEMBERS_UPDATE,
                self::ORDER_READ, self::ORDER_CREATE, self::ORDER_UPDATE,
                self::ITEM_READ, self::ITEM_CREATE, self::ITEM_UPDATE, self::ITEM_DELETE,
                self::MENU_READ, self::MENU_UPDATE, self::MENU_CREATE, self::MENU_DELETE,
                self::PLANNING_READ, self::PLANNING_CREATE, self::PLANNING_UPDATE, self::PLANNING_DELETE,
                self::SALES_READ, self::SALES_ARCHIVE, self::SALES_DELETE, self::SALES_UPDATE,
                self::DEVICES_READ, self::DEVICES_UPDATE,
                self::CUSTOMER_READ, self::CUSTOMER_CREATE, self::CUSTOMER_UPDATE, self::CUSTOMER_DELETE,
                self::TICKET_CREATE,

                self::SESSION_OPEN, self::SESSION_CLOSE,
                self::DRAWER_OPEN, self::DRAWER_PAYOUT, self::DRAWER_PAYIN,
                self::Z_REPORT_EXPORT,

                self::ORDER_VOID, self::ORDER_DISCOUNT, self::ORDER_REPRINT, self::ORDER_STATUS_SET,
                self::PAYMENT_CAPTURE, self::PAYMENT_REFUND, self::TIPS_ADD,

                self::PRICE_OVERRIDE, self::MENU_PUBLISH, self::MENU_SYNC,

                self::KIOSK_CONFIG_READ, self::KIOSK_CONFIG_UPDATE,
                self::WIFI_ORDER_READ, self::WIFI_ORDER_UPDATE,

                self::KDS_READ, self::KDS_UPDATE,

                self::PRINTER_READ, self::PRINTER_UPDATE, self::DEVICE_SYNC,

                self::STOCK_READ, self::STOCK_UPDATE, self::PURCHASE_READ,

                self::LOYALTY_READ, self::LOYALTY_UPDATE, self::COUPON_APPLY, self::VOUCHER_REDEEM,

                self::ANALYTICS_READ, self::EXPORTS_CREATE,

                self::DELIVERY_READ, self::DELIVERY_UPDATE,

                self::MEMBERS_LOGOUT, self::MEMBERS_AUTH,

                self::CATEGORY_READ, self::CATEGORY_CREATE, self::CATEGORY_UPDATE, self::CATEGORY_DELETE, self::CATEGORY_REORDER, self::CATEGORY_ACTIVATE,
                self::CREATE_INGREDIENT, self::INGREDIENT_READ, self::UPDATE_INGREDIENT, self::INGREDIENT_DELETE,
                self::OPTION_CREATE, self::OPTION_READ, self::UPDATE_OPTION, self::OPTION_DELETE
            ],
            StoreMemberRole::Cashier => [
                self::STORE_READ,
                self::ORDER_READ, self::ORDER_CREATE, self::ORDER_UPDATE,
                self::ITEM_READ,
                self::MENU_READ,
                self::CUSTOMER_READ, self::CUSTOMER_CREATE, self::CUSTOMER_UPDATE, self::CUSTOMER_DELETE,
                self::TICKET_CREATE,
                self::DEVICES_READ,

                self::SESSION_OPEN, self::SESSION_CLOSE, // selon ta politique, tu peux limiter CLOSE au manager
                self::DRAWER_OPEN, self::DRAWER_PAYOUT, self::DRAWER_PAYIN,

                self::ORDER_VOID, self::ORDER_DISCOUNT, self::ORDER_REPRINT, self::ORDER_STATUS_SET,
                self::PAYMENT_CAPTURE, self::TIPS_ADD,

                self::PRICE_OVERRIDE, // optionnel pour caissier

                self::KDS_READ,                            // lecture des bons
                self::PRINTER_READ, self::DEVICE_SYNC,

                self::COUPON_APPLY, self::VOUCHER_REDEEM,

                self::DELIVERY_READ,

                self::MEMBERS_LOGOUT, self::MEMBERS_AUTH,

                self::CATEGORY_READ,

                self::INGREDIENT_READ,
                self::OPTION_READ
            ],
            StoreMemberRole::Kitchen => [
                self::STORE_READ,
                self::ORDER_READ,
                self::MENU_READ,
                self::ITEM_READ,
                self::DEVICES_READ,
                self::TICKET_CREATE,

                self::KDS_READ, self::KDS_UPDATE,
                self::ORDER_STATUS_SET, // passer "in_prep" -> "ready" -> "served"
                self::PRINTER_READ,
                self::DEVICE_SYNC,
                self::MEMBERS_LOGOUT, self::MEMBERS_AUTH,
                self::CATEGORY_READ,
                self::INGREDIENT_READ,
                self::OPTION_READ
            ],
        };
    }

    public static function all(): array
    {
        $r = new ReflectionClass(self::class);
        return array_values($r->getConstants());
    }
}
