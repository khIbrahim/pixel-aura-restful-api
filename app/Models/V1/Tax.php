<?php

namespace App\Models\V1;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property string      $code
 * @property string      $name
 * @property string|null $description
 * @property string      $mode            percentage|fixed
 * @property string      $rate            Pourcentage (ex: 20.0000 = 20%)
 * @property string|null $amount          Montant fixe éventuel
 * @property bool        $inclusive       Indique si la taxe est incluse dans le prix (TTC) ou ajoutée (HT)
 * @property bool        $compound        Indique si la taxe s'applique après les autres taxes
 * @property int         $priority        Ordre de priorité pour l'application des taxes
 * @property string|null $country_code    Code ISO du pays (ex: FR, US)
 * @property string|null $region_code     Code de région/état/province
 * @property string      $applies_to      items|categories|shipping|orders|service_fees
 * @property bool        $active          Indique si la taxe est active
 * @property Carbon|null $starts_at       Date de début d'application
 * @property Carbon|null $ends_at         Date de fin d'application
 * @property int         $rounding_strategy  0=none 1=line 2=total 3=unit
 * @property int         $rounding_precision Précision d'arrondi (nombre de décimales)
 * @property string|null $external_id     Identifiant externe (intégration API)
 * @property array|null  $metadata        Métadonnées additionnelles
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 */
class Tax extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'taxes';

    protected $fillable = [
        'code',
        'name',
        'description',
        'mode',
        'rate',
        'amount',
        'inclusive',
        'compound',
        'priority',
        'country_code',
        'region_code',
        'applies_to',
        'active',
        'starts_at',
        'ends_at',
        'rounding_strategy',
        'rounding_precision',
        'external_id',
        'metadata',
    ];

    protected $casts = [
        'rate'               => 'decimal:4',
        'amount'             => 'decimal:4',
        'inclusive'          => 'boolean',
        'compound'           => 'boolean',
        'priority'           => 'integer',
        'active'             => 'boolean',
        'starts_at'          => 'datetime',
        'ends_at'            => 'datetime',
        'rounding_strategy'  => 'integer',
        'rounding_precision' => 'integer',
        'metadata'           => 'array',
    ];

    // Scopes --------------------------------------------------------------

    /**
     * Filtre les taxes actives.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Filtre les taxes applicables à une date donnée.
     */
    public function scopeInDate(Builder $query, ?Carbon $at = null): Builder
    {
        $at = $at ?: now();

        return $query
            ->where(function (Builder $q) use ($at) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at);
            })
            ->where(function (Builder $q) use ($at) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $at);
            });
    }

    /**
     * Filtre les taxes actives et applicables à une date donnée.
     */
    public function scopeApplicable(Builder $query, ?Carbon $at = null): Builder
    {
        return $query->active()->inDate($at);
    }

    /**
     * Filtre les taxes pour une région spécifique.
     */
    public function scopeForRegion(Builder $query, ?string $country, ?string $region = null): Builder
    {
        return $query
            ->when($country, fn (Builder $q) => $q->where('country_code', $country))
            ->when($region, fn (Builder $q) => $q->where('region_code', $region));
    }

    /**
     * Filtre par type de taxe (pourcentage ou montant fixe).
     */
    public function scopeByMode(Builder $query, string $mode): Builder
    {
        return $query->where('mode', $mode);
    }

    /**
     * Filtre par domaine d'application.
     */
    public function scopeByAppliesTo(Builder $query, string $appliesTo): Builder
    {
        return $query->where('applies_to', $appliesTo);
    }

    // Business logic -----------------------------------------------------

    /**
     * Vérifie si la taxe est actuellement active à une date donnée.
     */
    public function isCurrentlyActive(?Carbon $at = null): bool
    {
        if (!$this->active) {
            return false;
        }
        $at = $at ?: now();

        if ($this->starts_at && $this->starts_at->gt($at)) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->lt($at)) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie si la taxe est de type pourcentage.
     */
    public function isPercentage(): bool
    {
        return $this->mode === 'percentage';
    }

    /**
     * Vérifie si la taxe est de type montant fixe.
     */
    public function isFixed(): bool
    {
        return $this->mode === 'fixed';
    }

    /**
     * Calcule le montant de taxe pour un montant de base.
     * Si la taxe est inclusive et que $baseIsGross=true, on extrait la taxe contenue.
     *
     * @param float|int $baseAmount Montant de base (ex: prix HT ou TTC selon $baseIsGross)
     * @param bool $baseIsGross Indique si le montant fourni inclut déjà la taxe (pour taxes inclusives)
     * @return float Montant de la taxe
     */
    public function computeTaxAmount(float|int $baseAmount, bool $baseIsGross = false): float
    {
        $base = (float) $baseAmount;
        if ($base <= 0) {
            return 0.0;
        }

        if ($this->isFixed()) {
            return $this->applyRounding((float) $this->amount);
        }

        $rateDecimal = ((float) $this->rate) / 100;
        if ($rateDecimal <= 0) {
            return 0.0;
        }

        if ($this->inclusive && $baseIsGross) {
            // Montant TTC fourni : on extrait la taxe = TTC - (TTC / (1 + rate))
            $net = $base / (1 + $rateDecimal);
            $tax = $base - $net;
            return $this->applyRounding($tax);
        }

        // Taxe ajoutée sur le montant (considéré net / HT)
        $tax = $base * $rateDecimal;
        return $this->applyRounding($tax);
    }

    /**
     * Retourne le prix total (base + taxe) selon que la taxe est inclusive ou non.
     *
     * @param float|int $baseAmount Montant de base
     * @param bool $baseIsGross Indique si le montant fourni inclut déjà la taxe
     * @return float Montant total avec taxe
     */
    public function computeTotalAmount(float|int $baseAmount, bool $baseIsGross = false): float
    {
        $base = (float) $baseAmount;

        if ($this->inclusive) {
            return $base; // Déjà inclus
        }

        $tax = $this->computeTaxAmount($base, $baseIsGross);
        return $this->applyRounding($base + $tax);
    }

    /**
     * Applique la stratégie d'arrondi configurée.
     *
     * @param float $value Valeur à arrondir
     * @return float Valeur arrondie
     */
    protected function applyRounding(float $value): float
    {
        if ($this->rounding_strategy === 0) {
            return $value; // Pas de rounding ici (traitement global ailleurs)
        }

        $precision = $this->rounding_precision ?? 2;
        return round($value, $precision, PHP_ROUND_HALF_UP);
    }

    /**
     * Retourne une représentation lisible du taux ou montant de taxe.
     */
    public function getDisplayRateAttribute(): string
    {
        if ($this->isFixed()) {
            return number_format((float) $this->amount, 2);
        }

        return number_format((float) $this->rate, 2) . '%';
    }

    /**
     * Retourne une description complète de la taxe incluant code, nom et taux.
     */
    public function getFullDescriptionAttribute(): string
    {
        $parts = [
            $this->code,
            $this->name,
        ];

        return implode(' - ', array_filter($parts));
    }
}
