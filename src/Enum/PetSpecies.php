<?php

namespace App\Enum;

enum PetSpecies: string
{
    case CAT = 'cat';
    case DOG = 'dog';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::CAT => 'Кошка',
            self::DOG => 'Собака',
            self::OTHER => 'Иное',
        };
    }
}
