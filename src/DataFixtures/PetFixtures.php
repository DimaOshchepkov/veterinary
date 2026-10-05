<?php

namespace App\DataFixtures;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetGender;
use App\Enum\PetSpecies;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $petsData = [
            // ========== Питомцы клиента 1 (ivanov) ==========
            [
                'reference' => 'pet-ivanov-1',
                'name' => 'Барсик',
                'species' => PetSpecies::CAT,
                'gender' => PetGender::MALE,
                'birthDate' => new \DateTimeImmutable('2020-05-15'), // Дата в прошлом
                'breed' => 'Мейн-кун',
                'ownerRef' => 'user-client-1',
            ],
            [
                'reference' => 'pet-ivanov-2',
                'name' => 'Шарик',
                'species' => PetSpecies::DOG,
                'gender' => PetGender::MALE,
                'birthDate' => new \DateTimeImmutable('2021-08-10'),
                'breed' => 'Лабрадор',
                'ownerRef' => 'user-client-1',
            ],

            // ========== Питомцы клиента 2 (petrova) ==========
            [
                'reference' => 'pet-petrova-1',
                'name' => 'Муся',
                'species' => PetSpecies::CAT,
                'gender' => PetGender::FEMALE,
                'birthDate' => new \DateTimeImmutable('2022-01-20'),
                'breed' => 'Британская короткошерстная',
                'ownerRef' => 'user-client-2',
            ],

            // ========== Питомцы клиента 3 (sidorov) ==========
            // Нужен для тестов пагинации и создания множества заявок
            [
                'reference' => 'pet-sidorov-1',
                'name' => 'Бобик',
                'species' => PetSpecies::DOG,
                'gender' => PetGender::MALE,
                'birthDate' => new \DateTimeImmutable('2019-11-05'),
                'breed' => 'Дворняга',
                'ownerRef' => 'user-client-3',
            ],

            // Клиент 4 (smirnova) намеренно отсутствует здесь,
            // чтобы протестировать сценарий "У пользователя нет питомцев"
        ];

        foreach ($petsData as $data) {
            $pet = new Pet();
            $pet->setName($data['name']);
            $pet->setSpecies($data['species']);
            $pet->setGender($data['gender']);
            $pet->setBirthDate($data['birthDate']);
            $pet->setBreed($data['breed']);

            $owner = $this->getReference($data['ownerRef'], User::class);
            $pet->setOwner($owner);

            $manager->persist($pet);

            $this->addReference($data['reference'], $pet);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
