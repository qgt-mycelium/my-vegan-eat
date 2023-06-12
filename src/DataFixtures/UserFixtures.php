<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $userJane = (new User());
        $userJane->setEmail('jane.doe@example.com');
        $userJane->setUsername('Jane');
        $userJane->setPassword($this->passwordHasher->hashPassword($userJane, 'password'));

        $manager->persist($userJane);
        $manager->flush();
    }
}
