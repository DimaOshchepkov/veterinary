<?php

namespace App\DataFixtures;

use App\Entity\VeterinaryService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface; // ← ДОБАВЬТЕ
use Doctrine\Persistence\ObjectManager;

class VeterinaryServiceFixtures extends Fixture implements DependentFixtureInterface // ← ДОБАВЬТЕ implements
{
    public function load(ObjectManager $manager): void
    {
        $servicesData = [
            [
                'reference' => 'service-1',
                'name' => 'Первичный осмотр терапевта',
                'description' => 'Комплексный осмотр животного, измерение температуры, аускультация, сбор анамнеза.',
                'price' => 150000,
            ],
            [
                'reference' => 'service-2',
                'name' => 'Вакцинация комплексная (собаки)',
                'description' => 'Вакцинация против чумы, энтерита, гепатита, лептоспироза и бешенства.',
                'price' => 280000,
            ],
            [
                'reference' => 'service-3',
                'name' => 'Вакцинация комплексная (кошки)',
                'description' => 'Вакцинация против панлейкопении, ринотрахеита, калицивироза и бешенства.',
                'price' => 250000,
            ],
            [
                'reference' => 'service-4',
                'name' => 'Стрижка когтей',
                'description' => 'Гигиеническая процедура обрезки когтей у собак и кошек.',
                'price' => 50000,
            ],
            [
                'reference' => 'service-5',
                'name' => 'УЗИ брюшной полости',
                'description' => 'Ультразвуковое исследование органов ЖКТ, мочеполовой системы.',
                'price' => 200000,
            ],
            [
                'reference' => 'service-6',
                'name' => 'Чистка зубов ультразвуком',
                'description' => 'Профессиональная гигиена полости рта с использованием ультразвукового скалера.',
                'price' => 350000,
            ],
            [
                'reference' => 'service-7',
                'name' => 'Вызов врача на дом',
                'description' => 'Первичный осмотр и консультация ветеринарного врача на дому у пациента.',
                'price' => 300000,
            ],
        ];

        foreach ($servicesData as $data) {
            $service = new VeterinaryService();
            $service->setName($data['name']);
            $service->setDescription($data['description']);
            $service->setPrice($data['price']);

            $manager->persist($service);

            $this->addReference($data['reference'], $service);
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
