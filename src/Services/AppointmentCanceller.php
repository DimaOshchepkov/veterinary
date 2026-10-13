<?php

namespace App\Services;

use App\Entity\Appointment;
use App\Exception\AppointmentCannotBeCancelledException;
use Doctrine\ORM\EntityManagerInterface;

readonly class AppointmentCanceller
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /** @throws AppointmentCannotBeCancelledException */
    public function cancel(Appointment $appointment): void
    {
        $appointment->cancel();
        $this->em->flush();
    }
}
