<?php

namespace App\Services;

use App\Dto\AppointmentRequest;
use App\Entity\Appointment;
use App\Entity\User;
use App\Enum\AppointmentLocation;
use App\Repository\PetRepository;
use App\Repository\VeterinaryServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class AppointmentService
{
    public function __construct(
        private EntityManagerInterface      $em,
        private ValidatorInterface          $validator,
        private PetRepository               $petRepo,
        private VeterinaryServiceRepository $serviceRepo,
    ) {}

    public function createFromRequest(AppointmentRequest $dto, User $owner): Appointment
    {
        $pet = $this->petRepo->find($dto->pet);
        if (!$pet || $pet->getOwner() !== $owner) {
            throw new \InvalidArgumentException('Питомец не найден');
        }

        $service = $this->serviceRepo->find($dto->service);
        if (!$service) {
            throw new \InvalidArgumentException('Услуга не найдена');
        }

        $location = $dto->getLocationEnum();
        if (!$location) {
            throw new \InvalidArgumentException('Неверный тип локации');
        }

        $appointment = new Appointment();
        $appointment->setOwner($owner);
        $appointment->setPet($pet);
        $appointment->setService($service);
        $appointment->setLocation($location);
        $appointment->setAppointmentDate($dto->date);
        $appointment->setAppointmentTime($dto->time);

        if ($location === AppointmentLocation::HOME) {
            $appointment->setAddress($dto->address);
        }

        return $appointment;
    }

    public function save(Appointment $appointment): void
    {
        $this->em->persist($appointment);
        $this->em->flush();
    }

    public function validate(Appointment $appointment): array
    {
        $errors = $this->validator->validate($appointment);
        $violations = [];

        foreach ($errors as $error) {
            $fieldMap = [
                'appointmentDate' => 'date',
                'appointmentTime' => 'time',
            ];
            $path = $error->getPropertyPath();
            $violations[] = [
                'propertyPath' => $fieldMap[$path] ?? $path,
                'title' => $error->getMessage(),
            ];
        }

        return $violations;
    }
}
