<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /* ---------- Custom queries ---------- */

    /**
     * Find published posts ordered by newest.
     *
     * @param int|null $maxResults
     *
     * @return Post[]
     */
    public function findPublishedOrderedByNewest(int|null $maxResults = null): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.publishedAt IS NOT NULL')
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($maxResults);

        /** @var Post[] $posts */
        $posts = $query->getQuery()->getResult();

        $this->hydrateTags($posts);
        $this->hydrateLikes($posts);

        return $posts;
    }

    /* ---------- Private functions ---------- */

    /**
     * Hydrate the tags of the posts.
     *
     * @param Post[] $posts
     */
    private function hydrateTags($posts): void
    {
        $tags = $this->getEntityManager()
            ->getRepository(Tag::class)
            ->findAllOrderedByNameForPosts($posts);

        foreach ($posts as $post) {
            /** @var Tag[]|array $postTags */
            $postTags = $tags[$post->getId()] ?? [];
            $post->setTags($postTags);
        }
    }

    /**
     * Hydrate the likes of the posts.
     *
     * @param Post[] $posts
     */
    private function hydrateLikes($posts): void
    {
        // Get the posts ids
        $postsIds = array_map(function ($post) {
            return $post->getId();
        }, $posts);

        // Get the posts with their likes
        /** @var Post[] $postWithLikes */
        $postWithLikes = $this->createQueryBuilder('p')
            ->select('p', 'l')
            ->join('p.likes', 'l')
            ->where('p.id IN (:posts_ids)')
            ->setParameter('posts_ids', $postsIds)
            ->getQuery()
            ->getResult();

        // Create an array with the post id as key and an array of likes as value
        $likesByPostId = [];
        foreach ($postWithLikes as $post) {
            foreach ($post->getLikes() as $like) {
                $likesByPostId[$post->getId()][] = $like;
            }
        }

        // Set the likes of each post
        foreach ($posts as $post) {
            $post->setLikes($likesByPostId[$post->getId()] ?? []);
        }
    }
}
