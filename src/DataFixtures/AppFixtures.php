<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("dahbi.essakali.sofiane@gmail.com");
        $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            "07101992"
        ));
        $user->setRoles(['ROLE_USER']);

        $manager->persist($user);
        $manager->flush();
    }
}
