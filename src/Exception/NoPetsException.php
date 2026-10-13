<?php

namespace App\Exception;

final class NoPetsException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Сначала добавьте хотя бы одного питомца.');
    }
}
