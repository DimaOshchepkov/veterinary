<?php

namespace App\Dto;

use App\Enum\AppointmentLocation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AppointmentRequest
{
    #[Assert\NotBlank(message: 'Выберите питомца')]
    #[Assert\Positive(message: 'Неверный ID питомца')]
    public ?int $pet = null;

    #[Assert\NotBlank(message: 'Выберите услугу')]
    #[Assert\Positive(message: 'Неверный ID услуги')]
    public ?int $service = null;

    #[Assert\NotBlank(message: 'Выберите место приёма')]
    public ?string $location = null;


    public ?string $address = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->location === AppointmentLocation::HOME && !$this->address) {
            $context->buildViolation('Адрес обязателен при приеме на дому')
                ->atPath('address')
                ->addViolation();
        }
    }

    #[Assert\NotBlank(message: 'Укажите дату')]
    #[Assert\Type(type: \DateTimeImmutable::class, message: 'Неверный формат даты')]
    #[Assert\GreaterThanOrEqual(
        value: 'today',
        message: 'Дата приема не может быть в прошлом'
    )]
    public ?\DateTimeImmutable $date = null;

    #[Assert\NotBlank(message: 'Укажите время')]
    #[Assert\Type(type: \DateTimeImmutable::class, message: 'Неверный формат времени')]
    public ?\DateTimeImmutable $time = null;

    public function getLocationEnum(): ?AppointmentLocation
    {
        if (!$this->location) {
            return null;
        }

        try {
            return AppointmentLocation::from($this->location);
        } catch (\ValueError) {
            return null;
        }
    }
}
