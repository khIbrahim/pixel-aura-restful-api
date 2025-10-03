<?php

declare(strict_types=1);

namespace App\Enum\V1;

use App\Constants\V1\StoreTokenAbilities as A;

enum DeviceType: string
{
    // Opérationnels
    case Pos          = 'pos';            // Caisse tactile (iPad/Android/Pc)
    case Kiosk        = 'kiosk';          // Borne libre-service
    case Kds          = 'kds';            // Kitchen Display System
    case Terminal     = 'terminal';       // Terminal de paiement (TPE)
    case CustomerDisplay = 'customer_display'; // Afficheur client/pole display

    // Périphériques / back-of-house
    case Printer      = 'printer';        // Imprimante tickets/production
    case LabelPrinter = 'label_printer';  // Imprimante d’étiquettes (prep/frigo)
    case CashDrawer   = 'cash_drawer';    // Tiroir-caisse
    case Scanner      = 'scanner';        // Scanner code-barres / QR
    case Scale        = 'scale';          // Balance connectée
    case Fiscal       = 'fiscal';         // Boîtier / registre fiscal
    case Hub          = 'hub';            // Passerelle/bridge (USB↔LAN, IoT)

    // --- Abilities par défaut (par type) ------------------------------------

    /**
     * Abilities conseillées pour un device donné.
     * Elles sont automatiquement "sanitisées" contre A::all().
     *
     * @return list<string>
     */
    public function getAbilities(): array
    {
        $map = match ($this) {
            self::Pos => [
                '*'
//                A::ORDER_READ, A::ORDER_CREATE, A::ORDER_UPDATE, A::ORDER_VOID, A::ORDER_DISCOUNT, A::ORDER_REPRINT,
//                A::ORDER_STATUS_SET,
//                A::PAYMENT_CAPTURE, A::PAYMENT_REFUND, A::TIPS_ADD,
//                A::SESSION_OPEN, A::SESSION_CLOSE, A::DRAWER_OPEN, A::DRAWER_PAYIN, A::DRAWER_PAYOUT,
//                A::MENU_READ, A::MENU_SYNC, A::PRICE_OVERRIDE,
//                A::CUSTOMER_READ, A::CUSTOMER_CREATE, A::CUSTOMER_UPDATE, A::COUPON_APPLY, A::VOUCHER_REDEEM,
//                A::PRINTER_READ, A::PRINTER_UPDATE, A::DEVICE_SYNC,
//                A::KDS_READ,
//                A::DELIVERY_READ, A::DELIVERY_UPDATE,
//                A::ANALYTICS_READ,
//                A::MEMBERS_AUTH, A::MEMBERS_LOGOUT
            ],
            self::Kiosk => [
                A::ORDER_CREATE, A::ORDER_STATUS_SET,
                A::MENU_READ, A::MENU_PUBLISH, A::MENU_SYNC,
                A::KIOSK_CONFIG_READ, A::KIOSK_CONFIG_UPDATE,
                A::WIFI_ORDER_READ, A::WIFI_ORDER_UPDATE,
                A::PRINTER_READ, A::DEVICE_SYNC,
                A::CUSTOMER_CREATE, // opt-in fidélité au kiosk
                A::COUPON_APPLY,
            ],
            self::Kds => [
                A::KDS_READ, A::KDS_UPDATE, A::KDS_ROUTE_RULES,
                A::ORDER_READ, A::ORDER_STATUS_SET,
                A::PRINTER_READ, A::DEVICE_SYNC,
            ],
            self::Terminal => [
                A::PAYMENT_CAPTURE, A::PAYMENT_REFUND,
                A::DEVICE_SYNC,
            ],
            self::CustomerDisplay => [
                A::MENU_READ, A::ORDER_READ, A::DEVICE_SYNC,
            ],
            self::Printer => [
                A::PRINTER_READ, A::PRINTER_UPDATE, A::DEVICE_SYNC,
            ],
            self::LabelPrinter => [
                A::PRINTER_READ, A::PRINTER_UPDATE, A::DEVICE_SYNC, A::ITEM_READ,
            ],
            self::CashDrawer => [
                A::DRAWER_OPEN, A::DRAWER_PAYIN, A::DRAWER_PAYOUT, A::Z_REPORT_EXPORT,
            ],
            self::Scanner => [
                A::ITEM_READ, A::ORDER_UPDATE, A::DEVICE_SYNC,
            ],
            self::Scale => [
                A::ITEM_READ, A::DEVICE_SYNC,
            ],
            self::Fiscal => [
                A::Z_REPORT_EXPORT, A::SALES_READ, A::EXPORTS_CREATE, A::AUDIT_READ, A::DEVICE_SYNC,
            ],
            self::Hub => [
                A::DEVICE_SYNC, A::PRINTER_READ, A::PRINTER_UPDATE,
            ],
        };

        // Filtre les abilities sur le référentiel connu.
        return array_values(array_intersect($map, A::all()));
    }

    // --- Métadonnées UI / business ------------------------------------------

    public function getDisplayName(): string
    {
        return match ($this) {
            self::Pos             => 'Caisse Enregistreuse',
            self::Kiosk           => 'Borne Libre-Service',
            self::Kds             => 'Écran Cuisine (KDS)',
            self::Printer         => 'Imprimante',
            self::LabelPrinter    => 'Imprimante d’étiquettes',
            self::Terminal        => 'Terminal de Paiement',
            self::CustomerDisplay => 'Afficheur Client',
            self::CashDrawer      => 'Tiroir-Caisse',
            self::Scanner         => 'Scanner Codes-barres',
            self::Scale           => 'Balance',
            self::Fiscal          => 'Boîtier Fiscal',
            self::Hub             => 'Passerelle / Hub',
        };
    }

    public function getEmoji(): string
    {
        return match ($this) {
            self::Pos             => '🧾',
            self::Kiosk           => '🖥️',
            self::Kds             => '🍳',
            self::Printer         => '🖨️',
            self::LabelPrinter    => '🏷️',
            self::Terminal        => '💳',
            self::CustomerDisplay => '🪧',
            self::CashDrawer      => '💵',
            self::Scanner         => '🔎',
            self::Scale           => '⚖️',
            self::Fiscal          => '📑',
            self::Hub             => '🧩',
        };
    }

    /**
     * Courte description marketing/fonctionnelle (affichable en UI).
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::Pos => "Poste principal pour saisir les commandes, encaisser (espèces/carte), imprimer les tickets et piloter les périphériques.",
            self::Kiosk => "Borne libre-service pour laisser les clients commander et payer en autonomie, avec synchronisation du menu.",
            self::Kds => "Écran cuisine temps réel pour préparer, bouncer les plats et faire remonter les statuts (en préparation, prêt, servi).",
            self::Printer => "Impression tickets caisse/production. Peut être routée par poste (chaud/froid/boissons) et par zones.",
            self::LabelPrinter => "Impression d’étiquettes ingrédients/allergènes/lot/ DLC pour prep, mise en vitrine et traçabilité.",
            self::Terminal => "Encaissement carte/CB, pourboires, annulation et remboursement selon les droits. Peut fonctionner en semi-intégré.",
            self::CustomerDisplay => "Affichage client (prix, remises, QR fidélité, signature pourboires) côté comptoir.",
            self::CashDrawer => "Gestion du tiroir-caisse : ouvertures, entrées/sorties, rapprochement et clôture Z.",
            self::Scanner => "Scan articles, codes promo, coupons, et bons de livraison. Accélère la saisie en caisse.",
            self::Scale => "Pesée connectée pour articles au poids avec transfert automatique vers la ligne de ticket.",
            self::Fiscal => "Module d’archivage/registre fiscal, export Z et conformité (selon pays).",
            self::Hub => "Bridge pour piloter imprimantes USB/RS232 et autres périphériques sur le réseau.",
        };
    }

    // --- Helpers métier rapides ---------------------------------------------

    public function isOperational(): bool
    {
        return in_array($this, [self::Pos, self::Kiosk, self::Kds, self::Terminal, self::CustomerDisplay], true);
    }

    public function isPeripheral(): bool
    {
        return !$this->isOperational();
    }

    public function canTakeOrders(): bool
    {
        return in_array($this, [self::Pos, self::Kiosk], true);
    }

    public function isKitchenRelated(): bool
    {
        return in_array($this, [self::Kds, self::Printer, self::LabelPrinter], true);
    }

    public function canCapturePayments(): bool
    {
        return $this === self::Terminal || $this === self::Pos || $this === self::Kiosk;
    }

    public function canPrint(): bool
    {
        return in_array($this, [self::Printer, self::LabelPrinter], true);
    }

    public function isSingletonPerStore(): bool
    {
        // certains équipements n’ont de sens qu’en peu d’exemplaires
        return in_array($this, [self::Fiscal, self::Hub], true);
    }

    /**
     * Recommandations d’appairage (utile pour un wizard d’installation).
     *
     * @return list<DeviceType>
     */
    public function pairingRecommendations(): array
    {
        return match ($this) {
            self::Pos => [self::Printer, self::CashDrawer, self::Terminal, self::CustomerDisplay, self::Scanner, self::Scale],
            self::Kiosk => [self::Printer, self::Terminal, self::Hub],
            self::Kds => [self::Printer],
            self::Terminal => [self::Pos, self::Kiosk],
            self::CustomerDisplay, self::CashDrawer => [self::Pos],
            self::Printer => [self::Pos, self::Kiosk, self::Kds, self::Hub],
            self::LabelPrinter, self::Scanner, self::Scale, self::Fiscal => [self::Pos, self::Hub],
            self::Hub => [self::Printer, self::LabelPrinter, self::Scanner, self::Scale, self::Fiscal],
        };
    }

    /**
     * Valide qu’un device possède bien une ability.
     */
    public function supportsAbility(string $ability): bool
    {
        return in_array($ability, $this->getAbilities(), true);
    }

    /**
     * Abilities additionnelles recommandées selon le rôle humain pilotant le device.
     * Utile si tu crées des tokens device-scoped + user-scoped.
     *
     * @return list<string>
     */
    public function recommendedAbilityDeltaForRole(StoreMemberRole $role): array
    {
        // on garde ça minimaliste pour éviter la sur-autorisation
        return match ($this) {
            self::Pos => match ($role) {
                StoreMemberRole::Owner, StoreMemberRole::Manager => [A::EXPORTS_CREATE, A::ANALYTICS_READ],
                StoreMemberRole::Cashier => array(),
                StoreMemberRole::Kitchen => [],
            },
            default => [],
        };
    }

    // --- Mappings utilitaires ------------------------------------------------

    /**
     * Icône (par ex. pour un Design System).
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::Pos             => 'pos-cash-register',
            self::Kiosk           => 'device-kiosk',
            self::Kds             => 'kitchen-display',
            self::Printer         => 'printer',
            self::LabelPrinter    => 'label-printer',
            self::Terminal        => 'payment-terminal',
            self::CustomerDisplay => 'customer-display',
            self::CashDrawer      => 'cash-drawer',
            self::Scanner         => 'barcode-scanner',
            self::Scale           => 'scale',
            self::Fiscal          => 'fiscal-box',
            self::Hub             => 'iot-hub',
        };
    }

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function tryFromName(string $name): ?self
    {
        $normalized = strtolower($name);
        foreach (self::cases() as $case) {
            if ($case->value === $normalized || strtolower($case->name) === $normalized) {
                return $case;
            }
        }
        return null;
    }
}
