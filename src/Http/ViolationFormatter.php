<?php

namespace App\Http;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ViolationFormatter
{
    private const FIELD_MAP = [
        'appointmentDate' => 'date',
        'appointmentTime' => 'time',
    ];

    public function format(ConstraintViolationListInterface $violations): array
    {
        $result = [];

        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();

            $result[] = [
                'propertyPath' => self::FIELD_MAP[$path] ?? $path,
                'title' => $violation->getMessage(),
            ];
        }

        return $result;
    }
}
