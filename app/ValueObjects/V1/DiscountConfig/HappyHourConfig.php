<?php

namespace App\ValueObjects\V1\DiscountConfig;

use App\Contracts\V1\Discount\Config\DiscountConfigInterface;
use App\Enum\V1\Discount\DiscountType;
use App\Traits\V1\DiscountConfig\DiscountConfigTrait;

class HappyHourConfig implements DiscountConfigInterface
{
    use DiscountConfigTrait;

    public function __construct(
        public array  $days       = [],
        public string $start_time = '00:00',
        public string $end_time   = '23:59',
    ){}

    public function validate(): bool
    {
        $this->validationErrors = [];

        if (empty($this->days)) {
            $this->validationErrors['days'] = "Au moins un jour doit être sélectionné";
        }

        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($this->days as $day) {
            if (! in_array($day, $validDays)) {
                $this->validationErrors['days'] = "Jour invalide: $day";
                break;
            }
        }

        if (! preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $this->start_time)) {
            $this->validationErrors['start_time'] = "Format d'heure invalide (HH:MM attendu)";
        }

        if (! preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $this->end_time)) {
            $this->validationErrors['end_time'] = "Format d'heure invalide (HH:MM attendu)";
        }

        if ($this->start_time >= $this->end_time) {
            $this->validationErrors['time_range'] = "L'heure de début doit être avant l'heure de fin";
        }

        return empty($this->validationErrors);
    }

    public function toArray(): array
    {
        return [
            'type'       => $this->getType()->value,
            'days'       => $this->days,
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            days: $data['days'] ?? [],
            start_time: $data['start_time'] ?? '00:00',
            end_time: $data['end_time'] ?? '23:59',
        );
    }

    public function getType(): DiscountType
    {
        return DiscountType::HappyHour;
    }

    public function isActiveNow(): bool
    {
        $now = now();
        $currentDay = strtolower($now->format('l'));

        if (! in_array($currentDay, $this->days)) {
            return false;
        }

        $currentTime = $now->format('H:i');
        return $currentTime >= $this->start_time && $currentTime <= $this->end_time;
    }

}
