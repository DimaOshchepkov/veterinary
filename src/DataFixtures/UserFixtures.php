<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            [
                'email' => 'admin@vetclinic.test',
                'plainPassword' => 'admin123',
                'roles' => ['ROLE_ADMIN', 'ROLE_USER'],
                'reference' => 'user-admin',
            ],

            [
                'email' => 'ivanov@vetclinic.test',
                'plainPassword' => 'user123',
                'roles' => ['ROLE_USER'],
                'reference' => 'user-client-1',
            ],
            [
                'email' => 'petrova@vetclinic.test',
                'plainPassword' => 'user123',
                'roles' => ['ROLE_USER'],
                'reference' => 'user-client-2',
            ],
            [
                'email' => 'sidorov@vetclinic.test',
                'plainPassword' => 'user123',
                'roles' => ['ROLE_USER'],
                'reference' => 'user-client-3',
            ],
            [
                'email' => 'smirnova@vetclinic.test',
                'plainPassword' => 'user123',
                'roles' => ['ROLE_USER'],
                'reference' => 'user-client-4',
            ],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setRoles($data['roles']);


            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $data['plainPassword']
            );
            $user->setPassword($hashedPassword);

            $manager->persist($user);

            $this->addReference($data['reference'], $user);
        }

        $manager->flush();
    }
}
