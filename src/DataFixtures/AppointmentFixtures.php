<?php

namespace App\DataFixtures;

use App\Entity\Appointment;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\VeterinaryService;
use App\Enum\AppointmentLocation;
use App\Enum\AppointmentStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppointmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $yesterday = (new \DateTimeImmutable())->modify('-1 day');
        $lastWeek = (new \DateTimeImmutable())->modify('-7 days');
        $tomorrow = (new \DateTimeImmutable())->modify('+1 day');
        $nextWeek = (new \DateTimeImmutable())->modify('+7 days');
        $inTwoWeeks = (new \DateTimeImmutable())->modify('+14 days');

        $appointmentsData = [
            [
                'petRef' => 'pet-ivanov-1',
                'serviceRef' => 'service-1',
                'location' => AppointmentLocation::SALON,
                'address' => null,
                'date' => $tomorrow,
                'time' => new \DateTimeImmutable('10:00'),
                'status' => AppointmentStatus::NEW,
                'ownerRef' => 'user-client-1',
            ],
            [
                'petRef' => 'pet-ivanov-2',
                'serviceRef' => 'service-2',
                'location' => AppointmentLocation::SALON,
                'address' => null,
                'date' => $nextWeek,
                'time' => new \DateTimeImmutable('14:30'),
                'status' => AppointmentStatus::CONFIRMED,
                'ownerRef' => 'user-client-1',
            ],
            [
                'petRef' => 'pet-ivanov-1',
                'serviceRef' => 'service-5',
                'location' => AppointmentLocation::HOME,
                'address' => 'г. Москва, ул. Ленина, д. 10, кв. 5',
                'date' => $inTwoWeeks,
                'time' => new \DateTimeImmutable('12:00'),
                'status' => AppointmentStatus::CANCELLED,
                'ownerRef' => 'user-client-1',
            ],

            [
                'petRef' => 'pet-petrova-1',
                'serviceRef' => 'service-6',
                'location' => AppointmentLocation::SALON,
                'address' => null,
                'date' => $lastWeek,
                'time' => new \DateTimeImmutable('15:00'),
                'status' => AppointmentStatus::COMPLETED,
                'ownerRef' => 'user-client-2',
            ],
            [
                'petRef' => 'pet-petrova-1',
                'serviceRef' => 'service-3',
                'location' => AppointmentLocation::SALON,
                'address' => null,
                'date' => $yesterday,
                'time' => new \DateTimeImmutable('11:00'),
                'status' => AppointmentStatus::CANCELLED,
                'ownerRef' => 'user-client-2',
            ],


            ...array_map(fn(int $i) => [
                'petRef' => 'pet-sidorov-1',
                'serviceRef' => 'service-1',
                'location' => AppointmentLocation::SALON,
                'address' => null,
                'date' => (new \DateTimeImmutable())->modify('+' . ($i + 1) . ' days'),
                'time' => new \DateTimeImmutable(sprintf('%02d:00', 9 + ($i % 10))),
                'status' => AppointmentStatus::NEW,
                'ownerRef' => 'user-client-3',
            ], range(1, 12)),
        ];

        foreach ($appointmentsData as $data) {
            $appointment = new Appointment();
            $appointment->setPet($this->getReference($data['petRef'], Pet::class));
            $appointment->setService($this->getReference($data['serviceRef'], VeterinaryService::class));
            $appointment->setLocation($data['location']);
            $appointment->setAddress($data['address']);
            $appointment->setAppointmentDate($data['date']);
            $appointment->setAppointmentTime($data['time']);
            $appointment->setStatus($data['status']);
            $appointment->setOwner($this->getReference($data['ownerRef'], User::class));

            $manager->persist($appointment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PetFixtures::class,
            VeterinaryServiceFixtures::class,
        ];
    }
}
