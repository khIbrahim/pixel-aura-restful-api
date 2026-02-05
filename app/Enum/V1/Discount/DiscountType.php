<?php

namespace App\Enum\V1\Discount;

enum DiscountType: string
{
    // Basiques
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';

    // Promotions
    case BuyXGetYFree = 'buy_x_get_y_free';

    // Temporels
    case HappyHour = 'happy_hour';

    // Livraison
    case FreeDelivery = 'free_delivery';
    case ReduceDelivery = 'reduce_delivery';

    // Clients
    case FirstOrder = 'first_order';

    case Quantity = 'quantity';

    public function requiresConfig(): bool
    {
        return in_array($this, [
            self::BuyXGetYFree,
            self::HappyHour,
        ]);
    }

    public function getValueRules(): array
    {
        return match($this) {
            self::Percentage, self::FirstOrder, self::HappyHour => [
                'required', 'numeric', 'min:0', 'between:0,100', 'max:100' // 0% à 100%
            ],

            self::FixedAmount, self::ReduceDelivery => [
                'required', 'numeric', 'min:1'
            ],

            self::Quantity => [
                'required', 'integer', 'min:1'
            ],

            self::FreeDelivery, self::BuyXGetYFree => [
                'nullable'
            ],
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Percentage     => 'Pourcentage',
            self::FixedAmount    => 'Montant fixe',
            self::BuyXGetYFree   => 'Achetez X obtenez Y',
            self::HappyHour      => 'Happy Hour',
            self::FreeDelivery   => 'Livraison gratuite',
            self::ReduceDelivery => 'Livraison réduite',
            self::FirstOrder     => 'Première commande',
            self::Quantity       => 'Remise quantité',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::Percentage     => 'Réduction en pourcentage sur la commande',
            self::FixedAmount    => 'Montant fixe déduit de la commande',
            self::BuyXGetYFree   => 'Offre promotionnelle sur quantité',
            self::HappyHour      => 'Réduction automatique selon horaires',
            self::FreeDelivery   => 'Frais de livraison offerts',
            self::ReduceDelivery => 'Réduction sur les frais de livraison',
            self::FirstOrder     => 'Bonus pour nouveaux clients',
            self::Quantity       => 'Réduction selon la quantité commandée',
        };
    }
}
