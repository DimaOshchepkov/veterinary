<?php

namespace App\Exception;

abstract class AppointmentException extends \DomainException
{
    public function __construct(
        string $message,
        private readonly string $propertyPath = 'root',
    ) {
        parent::__construct($message);
    }

    public function getPropertyPath(): string
    {
        return $this->propertyPath;
    }
}
