<?php

namespace App\Security\Voter;

use App\Entity\Appointment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class AppointmentVoter extends Voter
{
    public const CANCEL = 'APPOINTMENT_CANCEL';
    public const VIEW = 'APPOINTMENT_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::CANCEL, self::VIEW], true)
            && $subject instanceof Appointment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Appointment $subject */
        return $subject->getOwner() === $user;
    }
}
