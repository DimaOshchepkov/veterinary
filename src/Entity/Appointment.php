<?php

namespace App\Entity;

use App\Enum\AppointmentLocation;
use App\Enum\AppointmentStatus;
use App\Repository\AppointmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pet $pet = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VeterinaryService $service = null;

    #[ORM\Column(enumType: AppointmentLocation::class)]
    private ?AppointmentLocation $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->location === AppointmentLocation::HOME && !$this->address) {
            $context->buildViolation('Адрес обязателен при приеме на дому')
                ->atPath('address')
                ->addViolation();
        }
    }

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: 'Дата приема обязательна')]
    #[Assert\GreaterThanOrEqual(
        value: 'today',
        message: 'Дата приема не может быть в прошлом'
    )]
    private ?\DateTimeImmutable $appointmentDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    #[Assert\NotNull(message: 'Время приема обязательно')]
    private ?\DateTimeImmutable $appointmentTime = null;

    #[Assert\Callback]
    public function validateAppointmentTime(
        ExecutionContextInterface $context
    ): void {
        if ($this->appointmentTime === null) {
            return;
        }

        $now = new \DateTimeImmutable();

        if (
            $this->appointmentTime->format('Y-m-d') === $now->format('Y-m-d')
            && $this->appointmentTime < $now
        ) {
            $context
                ->buildViolation('Время приема не может быть в прошлом')
                ->atPath('appointmentTime')
                ->addViolation();
        }
    }



    #[ORM\Column(enumType: AppointmentStatus::class)]
    private ?AppointmentStatus $status = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->status = AppointmentStatus::NEW;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): static
    {
        $this->pet = $pet;

        return $this;
    }

    public function getService(): ?VeterinaryService
    {
        return $this->service;
    }

    public function setService(?VeterinaryService $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getLocation(): ?AppointmentLocation
    {
        return $this->location;
    }

    public function setLocation(AppointmentLocation $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getAppointmentDate(): ?\DateTimeImmutable
    {
        return $this->appointmentDate;
    }

    public function setAppointmentDate(\DateTimeImmutable $appointmentDate): static
    {
        $this->appointmentDate = $appointmentDate;

        return $this;
    }

    public function getAppointmentTime(): ?\DateTimeImmutable
    {
        return $this->appointmentTime;
    }

    public function setAppointmentTime(\DateTimeImmutable $appointmentTime): static
    {
        $this->appointmentTime = $appointmentTime;

        return $this;
    }

    public function getStatus(): ?AppointmentStatus
    {
        return $this->status;
    }

    public function setStatus(AppointmentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    public function transitionTo(AppointmentStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new \LogicException(sprintf(
                'Недопустимый переход статуса: из "%s" в "%s".',
                $this->status->label(),
                $newStatus->label()
            ));
        }

        $this->status = $newStatus;
    }


    public function cancel(): void
    {
        $this->transitionTo(AppointmentStatus::CANCELLED);
    }


    public function isEditable(): bool
    {
        return !$this->status->isFinal();
    }


    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    public function getLocationLabel(): string
    {
        return $this->location?->label() ?? '';
    }

    public function getPetDisplayName(): string
    {
        if (!$this->pet) {
            return '';
        }
        return sprintf('%s %s', $this->pet->getSpeciesLabel(), $this->pet->getName());
    }

}
