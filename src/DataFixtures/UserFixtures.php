<?php

namespace App\DataFixtures;

use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    // defines reference names for entities
    public const LUCAS = 'lucas';
    public const ALICE = 'alice';
    public const ADMIN = 'admin';

    private UserPasswordHasherInterface $hasher;

    /**
     * Generates initialization data for members :
     *  [email, plain text password, profile name, role, member reference]
     * @return \Generator
     */
    private static function membersGenerator()
    {
        yield ['lucas@localhost', '123456', 'Lucas', 'ROLE_USER', self::LUCAS];
        yield ['alice@localhost', '123456', 'Alice', 'ROLE_USER', self::ALICE];
        yield ['admin@localhost', '123456', 'Administrateur', 'ROLE_ADMIN', self::ADMIN];
    }

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::membersGenerator() as [$email, $plainPassword, $name, $role, $memberReference]) {
            $user = new Member();
            $password = $this->hasher->hashPassword($user, $plainPassword);
            $user->setEmail($email);
            $user->setName($name);
            $user->setPassword($password);
            $roles = array();
            $roles[] = $role;
            $user->setRoles($roles);
            $manager->persist($user);
            $manager->flush();
            $this->addReference($memberReference, $user);
        }
    }
}
