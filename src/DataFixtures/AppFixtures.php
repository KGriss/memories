<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\Memory;
use App\Entity\Mind;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->createMember(
            $manager,
            'lucas@localhost',
            'Souvenirs de voyage de Lucas',
            [
                ['Week-end à Paris', 'Promenade dans Paris et visite des principaux monuments.', 'Paris, France'],
                ['Vacances à Nice', 'Quelques jours sur la Côte d\'Azur.', 'Nice, France'],
                ['Découverte de Rome', 'Visite du Colisée et du centre historique.', 'Rome, Italie'],
            ]
        );

        $this->createMember(
            $manager,
            'alice@localhost',
            'Carnet de voyages d\'Alice',
            [
                ['Séjour à Londres', 'Découverte de Westminster et des musées.', 'Londres, Royaume-Uni'],
                ['Randonnée dans les Alpes', 'Randonnée avec plusieurs étapes en altitude.', 'Alpes, France'],
                ['Voyage à Lisbonne', 'Découverte des quartiers historiques.', 'Lisbonne, Portugal'],
            ]
        );

        $manager->flush();
    }

    private function createMember(
        ObjectManager $manager,
        string $email,
        string $mindDescription,
        array $memoriesData
    ): void {
        $member = new Member();
        $member->setEmail($email);
        $member->setRoles(['ROLE_USER']);
        $member->setPassword(
            $this->passwordHasher->hashPassword($member, '123456')
        );

        $mind = new Mind();
        $mind->setDescription($mindDescription);

        $member->setMind($mind);

        $manager->persist($member);
        $manager->persist($mind);

        foreach ($memoriesData as $memoryData) {
            $memory = new Memory();
            $memory->setName($memoryData[0]);
            $memory->setDescription($memoryData[1]);
            $memory->setLocation($memoryData[2]);
            $memory->setPicture(null);

            $mind->addMemory($memory);
            $manager->persist($memory);
        }
    }
}
