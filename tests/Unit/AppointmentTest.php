<?php

namespace App\Tests\Unit;

use App\Entity\Appointment;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\VeterinaryService;
use App\Enum\AppointmentLocation;
use App\Enum\AppointmentStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AppointmentTest extends TestCase
{
    #[Test]
    public function defaultStatusIsNew(): void
    {
        $appointment = new Appointment();

        $this->assertSame(AppointmentStatus::NEW, $appointment->getStatus());
    }

    #[Test]
    public function newAppointmentIsEditable(): void
    {
        $appointment = new Appointment();

        $this->assertTrue($appointment->isEditable());
    }


    #[Test]
    public function transitionFromNewToConfirmed(): void
    {
        $appointment = new Appointment();
        $appointment->transitionTo(AppointmentStatus::CONFIRMED);

        $this->assertSame(AppointmentStatus::CONFIRMED, $appointment->getStatus());
    }

    #[Test]
    public function transitionFromConfirmedToCompleted(): void
    {
        $appointment = new Appointment();
        $appointment->transitionTo(AppointmentStatus::CONFIRMED);
        $appointment->transitionTo(AppointmentStatus::COMPLETED);

        $this->assertSame(AppointmentStatus::COMPLETED, $appointment->getStatus());
    }

    #[Test]
    public function cancelFromNew(): void
    {
        $appointment = new Appointment();
        $appointment->cancel();

        $this->assertSame(AppointmentStatus::CANCELLED, $appointment->getStatus());
    }

    #[Test]
    public function cancelFromConfirmed(): void
    {
        $appointment = new Appointment();
        $appointment->transitionTo(AppointmentStatus::CONFIRMED);
        $appointment->cancel();

        $this->assertSame(AppointmentStatus::CANCELLED, $appointment->getStatus());
    }

    #[Test]
    public function fullLifecycleNewConfirmedCompleted(): void
    {
        $appointment = new Appointment();

        $this->assertSame(AppointmentStatus::NEW, $appointment->getStatus());
        $this->assertTrue($appointment->isEditable());

        $appointment->transitionTo(AppointmentStatus::CONFIRMED);
        $this->assertSame(AppointmentStatus::CONFIRMED, $appointment->getStatus());
        $this->assertTrue($appointment->isEditable());

        $appointment->transitionTo(AppointmentStatus::COMPLETED);
        $this->assertSame(AppointmentStatus::COMPLETED, $appointment->getStatus());
        $this->assertFalse($appointment->isEditable());
    }


    #[Test]
    public function cannotTransitionFromCompletedToAny(): void
    {
        $appointment = new Appointment();
        $appointment->transitionTo(AppointmentStatus::CONFIRMED);
        $appointment->transitionTo(AppointmentStatus::COMPLETED);

        $this->expectException(\LogicException::class);

        $appointment->transitionTo(AppointmentStatus::NEW);
    }

    #[Test]
    public function cannotTransitionFromCancelledToAny(): void
    {
        $appointment = new Appointment();
        $appointment->cancel();

        $this->expectException(\LogicException::class);

        $appointment->transitionTo(AppointmentStatus::CONFIRMED);
    }

    #[Test]
    public function cannotSkipConfirmedStatus(): void
    {
        $appointment = new Appointment();

        $this->expectException(\LogicException::class);

        $appointment->transitionTo(AppointmentStatus::COMPLETED);
    }

    #[Test]
    public function cannotCancelCompletedAppointment(): void
    {
        $appointment = new Appointment();
        $appointment->transitionTo(AppointmentStatus::CONFIRMED);
        $appointment->transitionTo(AppointmentStatus::COMPLETED);

        $this->expectException(\LogicException::class);

        $appointment->cancel();
    }


    #[Test]
    public function completedAppointmentIsNotEditable(): void
    {
        $appointment = new Appointment();
        $appointment->transitionTo(AppointmentStatus::CONFIRMED);
        $appointment->transitionTo(AppointmentStatus::COMPLETED);

        $this->assertFalse($appointment->isEditable());
    }

    #[Test]
    public function cancelledAppointmentIsNotEditable(): void
    {
        $appointment = new Appointment();
        $appointment->cancel();

        $this->assertFalse($appointment->isEditable());
    }
}
