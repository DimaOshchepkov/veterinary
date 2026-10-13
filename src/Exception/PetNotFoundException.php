<?php

namespace App\Exception;

final class PetNotFoundException extends AppointmentException
{
    public function __construct()
    {
        parent::__construct('Питомец не найден', 'pet');
    }
}
