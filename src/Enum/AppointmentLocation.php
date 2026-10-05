<?php

namespace App\Enum;

enum AppointmentLocation: string
{
    case SALON = 'salon';
    case HOME = 'home';

    public function label(): string
    {
        return match($this) {
            self::SALON => 'В салоне',
            self::HOME => 'На дому',
        };
    }
}
