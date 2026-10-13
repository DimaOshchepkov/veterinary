<?php

namespace App\Security\Voter;

use App\Entity\Pet;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PetVoter extends Voter
{
    public const EDIT = 'PET_EDIT';
    public const DELETE = 'PET_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Pet;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Pet $pet */
        $pet = $subject;

        return $pet->getOwner() === $user;
    }
}
