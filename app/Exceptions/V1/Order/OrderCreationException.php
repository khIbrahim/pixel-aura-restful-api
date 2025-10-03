<?php

namespace App\Exceptions\V1\Order;

use App\Exceptions\V1\BaseApiException;
use Throwable;

class OrderCreationException extends BaseApiException
{

    protected int $statusCode   = 400;
    protected string $errorType = 'ORDER_CREATION_ERROR';

    public static function noItemsProvided(): self
    {
        return new self("Aucun item n'a été spécifiée dans la commande");
    }

    public static function itemNotFound(int $id): self
    {
        return new self("L'item $id n'existe pas");
    }

    public static function itemVariantNotFound(int $id): self
    {
        return new self("La variante d'item $id n'existe pas");
    }

    public static function ingredientNotFound(int $id): self
    {
        return new self("L'ingrédient $id n'existe pas");
    }

    public static function invalidQuantity(int $id, int $quantity, int $min, int $max): self
    {
        return new self("La quantité $quantity pour le model $id est invalide. Elle doit être entre $min et $max");
    }

    public static function optionNotFound(int $id): self
    {
        return new self("L'option $id n'existe pas");
    }

    public static function invalidDineInDetails(string $message): self
    {
        return new self("Les détails pour le service en salle sont invalides: $message");
    }

    public static function missingDineInDetails(): self
    {
        return new self("Les détails pour le service en salle sont requis");
    }

    public static function invalidDeliveryDetails(string $message): self
    {
        return new self("Les détails pour le service de livraison sont invalides: $message");
    }

    public static function missingPickupDetails(): self
    {
        return new self("Les détails pour le service de cueillette sont requis");
    }

    public static function invalidPickupDetails(string $message): self
    {
        return new self("Les détails pour le service de cueillette sont invalides: $message");
    }

    public static function default(?Throwable $e): self
    {
        return new self("Une erreur est survenue lors de la création de la commande", previous: $e);
    }

}
