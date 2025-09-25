<?php

namespace App\Rules\V1;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

readonly class SkuRule implements ValidationRule
{

    public function __construct(
        private string $table,
        private ?int    $storeId = null
    ){}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail("L'attribut :$attribute doit être une chaîne non vide.");
            return;
        }

        if (strlen($value) > config('pos.item.sku.base_length') + config('pos.item.sku.variant_length') + 5) {
            $fail("L'attribut :$attribute dépasse la longueur maximale autorisée.");
            return;
        }

        if (preg_match('/^[A-Z0-9\-]+$/', $value)) {
            $fail("L'attribut :$attribute ne doit contenir que des lettres majuscules, des chiffres et des tirets.");
            return;
        }

        if (preg_match('/^-|-$|--/', $value)) {
            $fail("L'attribut :$attribute ne doit pas commencer ou se terminer par un tiret, ni contenir de tirets consécutifs.");
            return;
        }

        $exists = DB::table($this->table)
            ->where('sku', $value)
            ->when($this->storeId, fn($query) => $query->where('store_id', $this->storeId))
            ->exists();

        if($exists){
            $fail("L'attribut :$attribute doit être unique par magasin.");
        }
    }
}
