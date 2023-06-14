<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public const POST_REFERENCE = 'post';

    public function load(ObjectManager $manager): void
    {
        $post = (new Post());
        $post->setTitle('My first post');
        $post->setSlug('my-first-post');
        $post->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.');

        /** @var User $user */
        $user = $this->getReference(UserFixtures::USER_REFERENCE);
        $post->addLike($user);

        /** @var Tag */
        $tag = $this->getReference(TagFixtures::TAG_REFERENCE);
        $post->addTag($tag);

        $manager->persist($post);
        $manager->flush();

        $this->addReference(self::POST_REFERENCE, $post);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
        ];
    }
}
