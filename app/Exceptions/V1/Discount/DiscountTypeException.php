<?php

namespace App\Exceptions\V1\Discount;

use App\Exceptions\V1\BaseApiException;

class DiscountTypeException extends BaseApiException
{

    protected int $statusCode   = 400;
    protected string $errorType = "DISCOUNT_TYPE_ERROR";

    public static function invalidPercentageValue(float $value): self
    {
        return new self("La valeur de pourcentage de remise '$value' est invalide. Elle doit être comprise entre 0 et 100.");
    }

    public static function invalidFixedAmountValue(int $value): self
    {
        return new self("La valeur de montant fixe de remise '$value' est invalide. Elle doit être supérieure ou égale à 0.");
    }

    public static function missingConfig(string $type): self
    {
        return new self("La configuration est requise pour le type de remise '$type', mais elle est absente.");
    }

    public static function invalidConfig(string $type, array $errors): self
    {
        $errorMessages = implode("; ", array_map(fn($k, $v) => "$k: $v", array_keys($errors), $errors));
        return new self("La configuration pour le type de remise '$type' est invalide. Erreurs: $errorMessages");
    }

    public static function unsupportedType(string $type): self
    {
        return new self("Le type de remise '$type' n'est pas pris en charge.");
    }

}
