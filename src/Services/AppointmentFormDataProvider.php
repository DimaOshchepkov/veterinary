<?php

namespace App\Services;

use App\Entity\User;
use App\Exception\NoPetsException;
use App\Repository\PetRepository;
use App\Repository\VeterinaryServiceRepository;

readonly class AppointmentFormDataProvider
{
    public function __construct(
        private PetRepository $petRepo,
        private VeterinaryServiceRepository $serviceRepo,
    ) {}

    /**
     * @return array{pets: list<array{id: int, label: string}>, services: list<array{id: int, label: string}>}
     *
     * @throws NoPetsException
     */
    public function forUser(User $owner): array
    {
        $pets = $this->petRepo->findBy(['owner' => $owner]);

        if ([] === $pets) {
            throw new NoPetsException();
        }

        return [
            'pets' => array_map(static fn ($pet) => [
                'id' => $pet->getId(),
                'label' => $pet->getSpeciesLabel() . ' ' . $pet->getName(),
            ], $pets),
            'services' => array_map(static fn ($service) => [
                'id' => $service->getId(),
                'label' => $service->getName(),
            ], $this->serviceRepo->findAll()),
        ];
    }
}
