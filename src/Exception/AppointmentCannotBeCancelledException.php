<?php

namespace App\Exception;

final class AppointmentCannotBeCancelledException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Эту заявку уже нельзя отменить.');
    }
}
