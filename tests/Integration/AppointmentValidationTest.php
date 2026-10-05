<?php

namespace App\Tests\Integration;

use App\Entity\Appointment;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\VeterinaryService;
use App\Enum\AppointmentLocation;

use App\Enum\PetGender;
use App\Enum\PetSpecies;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\Test;

class AppointmentValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    private function createValidAppointment(): Appointment
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setPassword('hashed');

        $pet = new Pet();
        $pet->setName('Барсик');
        $pet->setSpecies(PetSpecies::CAT);
        $pet->setGender(PetGender::MALE);
        $pet->setBirthDate(new \DateTimeImmutable('2020-01-01'));
        $pet->setBreed('Мейн-кун');
        $pet->setOwner($user);

        $service = new VeterinaryService();
        $service->setName('Осмотр');
        $service->setDescription('Описание');
        $service->setPrice(1000);

        $appointment = new Appointment();
        $appointment->setPet($pet);
        $appointment->setService($service);
        $appointment->setLocation(AppointmentLocation::SALON);
        $appointment->setAppointmentDate(new \DateTimeImmutable('+1 day'));
        $appointment->setAppointmentTime(new \DateTimeImmutable('10:00'));
        $appointment->setOwner($user);

        return $appointment;
    }


    #[Test]
    public function addressIsRequiredForHomeLocation(): void
    {
        $appointment = $this->createValidAppointment();
        $appointment->setLocation(AppointmentLocation::HOME);
        $appointment->setAddress(null);

        $errors = $this->validator->validate($appointment);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('address', (string) $errors);
    }

    #[Test]
    public function addressIsOptionalForSalonLocation(): void
    {
        $appointment = $this->createValidAppointment();
        $appointment->setLocation(AppointmentLocation::SALON);
        $appointment->setAddress(null);

        $errors = $this->validator->validate($appointment);

        $addressErrors = array_filter(
            iterator_to_array($errors),
            fn($error) => $error->getPropertyPath() === 'address'
        );
        $this->assertCount(0, $addressErrors);
    }



    #[Test]
    public function pastDateIsInvalid(): void
    {
        $appointment = $this->createValidAppointment();
        $appointment->setAppointmentDate(new \DateTimeImmutable('-1 day'));

        $errors = $this->validator->validate($appointment);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('appointmentDate', (string) $errors);
    }



    #[Test]
    public function nullPetIsInvalid(): void
    {
        $appointment = $this->createValidAppointment();
        $appointment->setPet(null);

        $errors = $this->validator->validate($appointment);

        $this->assertGreaterThan(0, count($errors));
    }

    #[Test]
    public function nullServiceIsInvalid(): void
    {
        $appointment = $this->createValidAppointment();
        $appointment->setService(null);

        $errors = $this->validator->validate($appointment);

        $this->assertGreaterThan(0, count($errors));
    }

}
