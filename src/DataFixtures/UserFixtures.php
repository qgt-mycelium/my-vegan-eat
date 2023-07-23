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

    // Create some users (between 20 and 30) with random data and add them to the database
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();
        $countUsers = mt_rand(20, 30);
        $userPasswordTestLoop = mt_rand(1, $countUsers - 2);
        $userChangePasswordTestLoop = $countUsers;

        foreach (range(1, $countUsers) as $i) {
            $user = (new User());
            $user->setEmail($faker->email());
            $user->setUsername($userPasswordTestLoop == $i ? 'test_password' : ($userChangePasswordTestLoop == $i ? 'test_change_password' : $faker->userName()));
            // Hash the password with the password hasher, but use 'password' as password for the user with the username 'test_password'
            $user->setPassword($this->passwordHasher->hashPassword($user, in_array($i, [$userPasswordTestLoop, $userChangePasswordTestLoop]) ? 'password' : $faker->password(6, 20)));
            $manager->persist($user);
            $this->addReference('user_'.$i, $user);
        }

        // Create a user with the username 'test_admin' and the password 'password'
        $user = (new User());
        $user->setEmail($faker->email());
        $user->setUsername('test_admin');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        // Flush the users to the database
        $manager->flush();
    }
}
