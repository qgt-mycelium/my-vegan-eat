<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /* ---------- Custom queries ---------- */

    /**
     * Find all tags ordered by name for posts entities.
     *
     * @param Post[] $posts
     */
    public function findAllOrderedByNameForPosts(array $posts): ArrayCollection
    {
        // Get the tags for the posts
        $query = $this->createQueryBuilder('t')
            ->select('t', 'p')
            ->join('t.posts', 'p');

        // Get the posts ids
        $postsIds = array_unique(array_map(function ($post) {
            return $post->getId();
        }, $posts));

        // Filter the query by the posts ids
        $query->where('p.id IN (:postsIds)')
            ->setParameter('postsIds', $postsIds);

        // Get the tags
        /** @var Tag[] $tags */
        $tags = $query->getQuery()->getResult();

        // Create an array with the post id as key and an array of tags as value
        $tagsByPostId = [];
        foreach ($tags as $tag) {
            foreach ($tag->getPosts() as $post) {
                $tagsByPostId[$post->getId()][] = $tag;

                // Sort tags by name
                usort($tagsByPostId[$post->getId()], function ($a, $b) {
                    return $a->getName() <=> $b->getName();
                });
            }
        }

        // Return an ArrayCollection with the tags by post id
        return new ArrayCollection($tagsByPostId);
    }
}
