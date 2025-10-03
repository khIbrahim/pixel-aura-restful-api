<?php

namespace App\ValueObjects\V1;

use Brick\Money\Money as BrickMoney;
use Brick\Money\Context\CustomContext;
use JsonSerializable;
use NumberFormatter;

class Money implements JsonSerializable
{

    private const array ZERO_DECIMAL_CURRENCIES = [
        'DZD',
        'JPY',
        'KRW',
        'VND',
        'CLP',
        'ISK',
        'UGX',
        'XAF',
        'XOF',
        'BIF',
        'GNF',
        'KMF',
        'RWF',
        'PYG',
    ];

    private BrickMoney $money;

    public function __construct(BrickMoney $money)
    {
        $this->money = $money;
    }

    public static function ofMinor(int $amount, string $currency): self
    {
        if (self::isZeroDecimalCurrency($currency)) {
            return new self(
                BrickMoney::of($amount, $currency, new CustomContext(0))
            );
        }

        return new self(BrickMoney::ofMinor($amount, $currency));
    }

    public static function of(int|float|string $amount, string $currency): self
    {
        if (self::isZeroDecimalCurrency($currency)) {
            return new self(
                BrickMoney::of($amount, $currency, new CustomContext(0))
            );
        }

        return new self(BrickMoney::of($amount, $currency));
    }

    public static function isZeroDecimalCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), self::ZERO_DECIMAL_CURRENCIES, true);
    }

    public function getMinorAmount(): int
    {
        return $this->money->getMinorAmount()->toInt();
    }

    public function getAmount(): float
    {
        return $this->money->getAmount()->toFloat();
    }

    public function formatted(?string $locale = null): string
    {
        if (!$locale) {
            $locale = config('pos.locale', 'fr_DZ');
        }

        if (self::isZeroDecimalCurrency($this->getCurrency())) {
            return $this->formatZeroDecimal($locale);
        }

        return $this->money->formatTo($locale);
    }

    private function formatZeroDecimal(string $locale): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

        $formatted = $formatter->formatCurrency(
            $this->getAmount(),
            $this->getCurrency()
        );

        if ($formatted === false) {
            return number_format($this->getAmount(), 0, ',', ' ') . ' ' . $this->getCurrency();
        }

        return $formatted;
    }

    public function getCurrency(): string
    {
        return $this->money->getCurrency()->getCurrencyCode();
    }

    public function add(Money $other): self
    {
        return new self($this->money->plus($other->money));
    }

    public function subtract(Money $other): self
    {
        return new self($this->money->minus($other->money));
    }

    public function multiply(float|int|string $multiplier): self
    {
        return new self($this->money->multipliedBy($multiplier));
    }

    public function divide(float|int|string $divisor): self
    {
        return new self($this->money->dividedBy($divisor));
    }

    public function isZero(): bool
    {
        return $this->money->isZero();
    }

    public function isPositive(): bool
    {
        return $this->money->isPositive();
    }

    public function isNegative(): bool
    {
        return $this->money->isNegative();
    }

    public function jsonSerialize(): array
    {
        $currency = $this->money->getCurrency();
        $decimals = self::isZeroDecimalCurrency($this->getCurrency()) ? 0 : $currency->getDefaultFractionDigits();

        return [
            'amount'    => $this->getAmount(),
            'minor'     => $this->getMinorAmount(),
            'formatted' => $this->formatted(),
            'currency'  => $this->getCurrency(),
            'decimals'  => $decimals,
        ];
    }

    public function toArray(): array
    {
        return [
            'minor'     => $this->getMinorAmount(),
            'formatted' => $this->formatted(),
            'currency'  => $this->getCurrency(),
        ];
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
