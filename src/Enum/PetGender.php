<?php

namespace App\Enum;

enum PetGender: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    public function label(): string
    {
        return match($this) {
            self::MALE => 'М',
            self::FEMALE => 'Ж',
        };
    }
}
