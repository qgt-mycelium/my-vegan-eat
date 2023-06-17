<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class TagFixtures extends Fixture
{
    // Create some tags with faker (between 10 and 20) with random data and add them to the database
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        foreach (range(1, mt_rand(10, 20)) as $i) {
            $tag = (new Tag($faker->word()));
            $manager->persist($tag);
            $this->addReference('tag_'.$i, $tag);
        }

        $manager->flush();
    }
}
