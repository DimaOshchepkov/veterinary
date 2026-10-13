<?php

namespace App\Exception;

final class ServiceNotFoundException extends AppointmentException
{
    public function __construct()
    {
        parent::__construct('Услуга не найдена', 'service');
    }
}
