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


    public function isActive(): bool
    {
        return match($this) {
            self::NEW, self::CONFIRMED => true,
            self::COMPLETED, self::CANCELLED => false,
        };
    }


    public function isFinal(): bool
    {
        return match($this) {
            self::COMPLETED, self::CANCELLED => true,
            default => false,
        };
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
