<?php

namespace App\Enum\V1\Discount;

enum DiscountStatus: string
{

    case Draft     = 'draft'; // brouillon/en création
    case Active    = 'active'; // actif
    case Paused    = 'paused'; // en pause
    case Cancelled = 'canceled'; // annulé
    case Expired   = 'expired'; // expiré
    case Scheduled = 'scheduled'; // programmé
    case Depleted  = 'depleted'; // épuisé

    public function isStored(): bool
    {
        return in_array($this, [
            self::Draft,
            self::Active,
            self::Paused,
            self::Cancelled,
        ]);
    }

    public function isComputed(): bool
    {
        return in_array($this, [
            self::Scheduled,
            self::Expired,
            self::Depleted,
        ]);
    }

    public function isApplicable(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Brouillon',
            self::Scheduled => 'Programmé',
            self::Active    => 'Actif',
            self::Expired   => 'Expiré',
            self::Depleted  => 'Quota épuisé',
            self::Paused    => 'En pause',
            self::Cancelled  => 'Annulé',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        if ($newStatus->isComputed()) {
            return false;
        }

        return match($this) {
            self::Draft, self::Paused                                    => in_array($newStatus, [self::Active, self::Cancelled]),
            self::Active, self::Scheduled, self::Expired, self::Depleted => in_array($newStatus, [self::Paused, self::Cancelled]),
            self::Cancelled                                               => false,
        };
    }

    public function getIneligibilityMessage(?string $context = null): ?string
    {
        return match($this) {
            self::Draft     => "Ce discount est en brouillon",
            self::Scheduled => "Ce discount commence le $context",
            self::Expired   => "Ce discount a expiré le $context",
            self::Depleted  => "Le quota de ce discount est épuisé" . ($context ? " ($context)" : ""),
            self::Paused    => "Ce discount est en pause" . ($context ? " : $context" : ""),
            self::Cancelled  => "Ce discount a été annulé" . ($context ? " : $context" : ""),
            self::Active    => null,
        };
    }

}
