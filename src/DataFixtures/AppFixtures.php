<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\Memory;
use App\Entity\Mind;
use App\Entity\Travel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // defines reference names for instances of Memory
    private const PARIS_MEMORY = 'paris-memory';
    private const NICE_MEMORY = 'nice-memory';
    private const ROME_MEMORY = 'rome-memory';
    private const LONDON_MEMORY = 'london-memory';
    private const ALPS_MEMORY = 'alps-memory';
    private const LISBON_MEMORY = 'lisbon-memory';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Generates initialization data for travels :
     *  [name, description, published, memory references]
     * @return \Generator
     */
    private static function travelsGenerator()
    {
        yield ['Escapades en France', 'Petits séjours en France, entre Paris et la Méditerranée.', true, [self::PARIS_MEMORY, self::NICE_MEMORY]];
        yield ['Capitales européennes', 'Les grandes villes visitées en Europe.', false, [self::PARIS_MEMORY, self::ROME_MEMORY]];
        yield ['Villes d\'Europe', 'Londres et Lisbonne, deux séjours en ville.', true, [self::LONDON_MEMORY, self::LISBON_MEMORY]];
        yield ['Montagne', 'Randonnées et grand air.', false, [self::ALPS_MEMORY]];
    }

    public function load(ObjectManager $manager): void
    {
        $this->createMember(
            $manager,
            'lucas@localhost',
            'Souvenirs de voyage de Lucas',
            [
                ['Week-end à Paris', 'Promenade dans Paris et visite des principaux monuments.', 'Paris, France', self::PARIS_MEMORY],
                ['Vacances à Nice', 'Quelques jours sur la Côte d\'Azur.', 'Nice, France', self::NICE_MEMORY],
                ['Découverte de Rome', 'Visite du Colisée et du centre historique.', 'Rome, Italie', self::ROME_MEMORY],
            ]
        );

        $this->createMember(
            $manager,
            'alice@localhost',
            'Carnet de voyages d\'Alice',
            [
                ['Séjour à Londres', 'Découverte de Westminster et des musées.', 'Londres, Royaume-Uni', self::LONDON_MEMORY],
                ['Randonnée dans les Alpes', 'Randonnée avec plusieurs étapes en altitude.', 'Alpes, France', self::ALPS_MEMORY],
                ['Voyage à Lisbonne', 'Découverte des quartiers historiques.', 'Lisbonne, Portugal', self::LISBON_MEMORY],
            ]
        );

        $manager->flush();

        foreach (self::travelsGenerator() as [$name, $description, $published, $memoryReferences]) {
            $travel = new Travel();
            $travel->setName($name);
            $travel->setDescription($description);
            $travel->setPublished($published);
            foreach ($memoryReferences as $memoryReference) {
                // Retrieve the Memory instance from its reference name
                $memory = $this->getReference($memoryReference, Memory::class);
                $travel->addMemory($memory);
            }
            $manager->persist($travel);
        }
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
            $manager->flush();
            // Once the Memory instance has been saved to DB,
            // it has a valid Id and can be saved as a future reference
            $this->addReference($memoryData[3], $memory);
        }
    }
}
