<?php

namespace App\Enum;

enum AppointmentStatus: string
{
    case NEW = 'new';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::NEW => 'Новая',
            self::CONFIRMED => 'Подтверждена',
            self::COMPLETED => 'Завершена',
            self::CANCELLED => 'Отменена',
        };
    }

    /** @return list<self> */
    public static function activeCases(): array
    {
        return [self::NEW, self::CONFIRMED];
    }

    /** @return list<self> */
    public static function inactiveCases(): array
    {
        return [self::COMPLETED, self::CANCELLED];
    }

    public function isActive(): bool
    {
        return in_array($this, self::activeCases());
    }


    public function isFinal(): bool
    {
        return in_array($this, self::inactiveCases());
    }

    public function canTransitionTo(self $target): bool
    {
        return match($this) {
            self::NEW => in_array($target, [self::CONFIRMED, self::CANCELLED], true),
            self::CONFIRMED => in_array($target, [self::COMPLETED, self::CANCELLED], true),
            self::COMPLETED, self::CANCELLED => false,
        };
    }
}
