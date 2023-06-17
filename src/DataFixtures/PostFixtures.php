<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        // Create a random number of posts (between 10 and 20) with random data and add likes and tags randomly
        foreach (range(1, mt_rand(10, 20)) as $i) {
            $post = (new Post());
            $post->setTitle($faker->sentence());
            $post->setSlug($faker->slug());
            $post->setContent($faker->paragraph(mt_rand(3, 6), true));

            // Number of users
            $countUsers = $this->entityManager->getRepository(User::class)->count([]);

            // Number of tags
            $countTags = $this->entityManager->getRepository(Tag::class)->count([]);

            // Add between 1 and 5 likes to the post (if there are any users)
            if ($countUsers > 0 && 1 == mt_rand(0, 1)) {
                foreach (range(1, mt_rand(1, 5)) as $j) {
                    /** @var User $user */
                    $user = $this->getReference('user_'.mt_rand(1, $countUsers));
                    $post->addLike($user);
                }
            }

            // Add between 1 and 3 tags to the post (if there are any tags)
            if ($countTags > 0 && 1 == mt_rand(0, 1)) {
                foreach (range(1, mt_rand(1, 3)) as $j) {
                    /** @var Tag $tag */
                    $tag = $this->getReference('tag_'.mt_rand(1, $countTags));
                    $post->addTag($tag);
                }
            }

            // Publish randomly the post
            if (1 == mt_rand(0, 1)) {
                $post->setPublishedAt($faker->dateTimeBetween('-1 year', 'now'));
            }

            $manager->persist($post);
            $this->addReference('post_'.$i, $post);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
        ];
    }
}
