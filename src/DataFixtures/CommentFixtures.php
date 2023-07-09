<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        /** @var User[] $users */
        $users = $this->entityManager->getRepository(User::class)->findAll();

        /** @var Post[] $posts */
        $posts = $this->entityManager->getRepository(Post::class)->findAll();

        // Create a random number of comments (between 10 and 15) with random data and add likes and comments
        foreach (range(1, mt_rand(10, 15)) as $i) {
            $comment = (new Comment());
            $comment->setContent($faker->realText(mt_rand(10, 50)));
            $comment->setIsPublished($faker->boolean(80));
            $comment->setIsDeleted($faker->boolean(20));

            /** @var User $user */
            $user = $faker->randomElement($users);
            $comment->setAuthor($user);

            /** @var Post $post */
            $post = $faker->randomElement($posts);
            $comment->setPost($post);

            // Add between 1 and 5 likes to the comment (if there are any users)
            if (1 == mt_rand(0, 1)) {
                foreach (range(1, mt_rand(1, 5)) as $j) {
                    $reference = 'user_'.mt_rand(0, count($users) - 1);
                    if ($this->hasReference($reference)) {
                        /** @var User $user */
                        $user = $this->getReference($reference);
                        $comment->addLike($user);
                    }
                }
            }
            $manager->persist($comment);
            // Add between 1 and 3 child comments to the comment (if there are any users)
            if (1 == mt_rand(0, 1)) {
                $this->addComments($manager, $comment, mt_rand(1, 3));
            }
        }

        $manager->flush();
    }

    // This method add child comments to parent comment
    private function addComments(ObjectManager $manager, Comment $comment, int $count): void
    {
        $faker = \Faker\Factory::create();
        $users = $this->entityManager->getRepository(User::class)->findAll();

        foreach (range(1, $count) as $i) {
            $childComment = (new Comment());
            $childComment->setContent($faker->realText(mt_rand(10, 50)));
            $childComment->setIsPublished($faker->boolean(80));
            $childComment->setIsDeleted($faker->boolean(20));

            /** @var User $user */
            $user = $faker->randomElement($users);
            $childComment->setAuthor($user);

            $childComment->setPost($comment->getPost());
            $childComment->setParent($comment);
            $manager->persist($childComment);
            // Add between 1 and 5 likes to the comment (if there are any users)
            if (1 == mt_rand(0, 1)) {
                foreach (range(1, mt_rand(1, 5)) as $k) {
                    $reference = 'user_'.mt_rand(0, count($users) - 1);
                    if ($this->hasReference($reference)) {
                        /** @var User $user */
                        $user = $this->getReference($reference);
                        $childComment->addLike($user);
                    }
                }
            }
            $manager->persist($childComment);
            // Add between 1 and 2 child comments to the comment (if there are any users)
            if (1 == mt_rand(0, 1)) {
                $this->addComments($manager, $childComment, mt_rand(1, 2));
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PostFixtures::class,
        ];
    }
}
