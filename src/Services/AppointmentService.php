<?php

namespace App\Services;

use App\Dto\AppointmentRequest;
use App\Entity\Appointment;
use App\Entity\User;
use App\Enum\AppointmentLocation;
use App\Exception\PetNotFoundException;
use App\Exception\ServiceNotFoundException;
use App\Repository\PetRepository;
use App\Repository\VeterinaryServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class AppointmentService
{
    public function __construct(
        private ValidatorInterface $validator,
        private PetRepository $petRepo,
        private VeterinaryServiceRepository $serviceRepo,
    ) {}

    public function build(AppointmentRequest $dto, User $owner): Appointment
    {
        $pet = $this->petRepo->findOneBy(['id' => $dto->pet, 'owner' => $owner])
            ?? throw new PetNotFoundException();

        $service = $this->serviceRepo->find($dto->service)
            ?? throw new ServiceNotFoundException();

        $appointment = new Appointment();
        $appointment->setOwner($owner);
        $appointment->setPet($pet);
        $appointment->setService($service);
        $appointment->setLocation($dto->location);
        $appointment->setAppointmentDate($dto->date);
        $appointment->setAppointmentTime($dto->time);

        if ($dto->location === AppointmentLocation::HOME) {
            $appointment->setAddress($dto->address);
        }

        $violations = $this->validator->validate($appointment);
        if (count($violations) > 0) {
            throw new ValidationFailedException($appointment, $violations);
        }

        return $appointment;
    }
}
