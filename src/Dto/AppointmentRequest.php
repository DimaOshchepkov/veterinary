<?php

namespace App\Dto;

use App\Enum\AppointmentLocation;
use Symfony\Component\Validator\Constraints as Assert;

final class AppointmentRequest
{
    #[Assert\NotNull(message: 'Выберите питомца')]
    public ?int $pet = null;

    #[Assert\NotNull(message: 'Выберите услугу')]
    public ?int $service = null;

    #[Assert\NotBlank(message: 'Выберите место')]
    public ?AppointmentLocation $location = null;

    public ?string $address = null;

    #[Assert\NotNull(message: 'Укажите дату')]
    public ?\DateTimeImmutable $date = null;

    #[Assert\NotNull(message: 'Укажите время')]
    public ?\DateTimeImmutable $time = null;
}
