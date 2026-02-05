<?php

namespace App\Rules\V1;

use App\Casts\V1\Discount\DiscountConfigCast;
use App\Contracts\V1\Discount\Config\DiscountConfigInterface;
use App\Enum\V1\Discount\DiscountType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ReflectionClass;
use ReflectionException;

class DiscountConfigRule implements ValidationRule
{

    public function __construct(
        public string $type_value,
    ){}

    /**
     * @throws ReflectionException
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $type = DiscountType::tryFrom($this->type_value);

        if(! $type){
            $fail("Le type de réduction est invalide.");
            return;
        }

        if(! is_array($value)){
            $fail("La configuration de la réduction doit être un tableau.");
            return;
        }

        if (! $type->requiresConfig()){
            if (! empty($value)) {
                $fail("Le type de réduction '$type->value' ne nécessite pas de configuration.");
            }
            return;
        }

        $config = DiscountConfigCast::getTypeClass($type);
        if(! $config){
            $fail("Le type de réduction n'a pas de configuration associée.");
            return;
        }

        $reflection = new ReflectionClass($config);
        if(! $reflection->implementsInterface(DiscountConfigInterface::class)){
            $fail("La configuration de la réduction n'implémente pas l'interface requise.");
            return;
        }

        $params = $reflection->getConstructor()?->getParameters();
        if($params && count($params) > 0){
            foreach ($params as $param) {
                if(! $param->isOptional() && ! array_key_exists($param->getName(), $value)){
                    $fail("La configuration de la réduction est invalide, le champ requis (" . $param->getName() . ") est manquant.");
                    return;
                }
            }
        }

        try {
            $configInstance = $config::fromArray($value);

            if (!$configInstance->validate()) {
                foreach ($configInstance->getValidationErrors() as $field => $error) {
                    $fail("config.$field: {$error}");
                }
            }
        } catch (\Throwable $e) {
            $fail("Erreur lors de la création de la configuration: {$e->getMessage()}");
        }
    }
}
