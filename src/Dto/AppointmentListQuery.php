<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class AppointmentListQuery
{
    public const array LIMITS = [10, 20, 50, 100];

    #[Assert\Choice(choices: ['active', 'inactive'])]
    public string $status = 'active';

    #[Assert\Choice(choices: self::LIMITS)]
    public int $limit = 10;

    #[Assert\Positive]
    public int $page = 1;

    public function isActive(): bool
    {
        return $this->status !== 'inactive';
    }

    public function toRouteParams(): array
    {
        return [
            'status' => $this->status,
            'limit' => $this->limit,
            'page' => $this->page,
        ];
    }
}
