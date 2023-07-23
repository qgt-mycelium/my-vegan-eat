<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\Post;
use App\Entity\User;
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
     * Find published posts good to know ordered by newest.
     *
     * @return Post[]
     */
    public function findPublishedGoodToKnowOrderedByNewest(): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.publishedAt IS NOT NULL')
            ->andWhere('p.isGoodToKnow = true')
            ->orderBy('p.publishedAt', 'DESC');

        /** @var Post[] $posts */
        $posts = $query->getQuery()->getResult();

        return $posts;
    }

    /**
     * Find favorite posts for a user.
     *
     * @return Post[]
     */
    public function findFavoritePostsForUser(User $user): array
    {
        /** @var Post[] $posts */
        $posts = $this->createQueryBuilder('p')
            ->select('p')
            ->join('p.favorites', 'f')
            ->where('f.id = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $this->hydrateTags($posts);
        $this->hydrateLikes($posts);
        $this->hydrateFavorites($posts);

        return $posts;
    }

    /**
     * Find popular posts ordered by most liked.
     *
     * @return Post[]
     */
    public function findPopularOrderedByMostLiked(int $maxResults = null): array
    {
        /** @var Post[] $posts */
        $posts = $this->createQueryBuilder('p')
            ->select('p')
            ->join('p.likes', 'l')
            ->where('p.publishedAt IS NOT NULL')
            ->groupBy('p.id')
            ->orderBy('COUNT(l.id)', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();

        return $posts;
    }

    /**
     * Find published posts ordered by newest.
     *
     * @return Post[]
     */
    public function findPublishedOrderedByNewest(int $maxResults = null): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.publishedAt IS NOT NULL')
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($maxResults);

        /** @var Post[] $posts */
        $posts = $query->getQuery()->getResult();

        return $posts;
    }

    /* ---------- Hydrate functions ---------- */

    /**
     * Hydrate the tags of the posts.
     *
     * @param Post[] $posts
     */
    public function hydrateTags($posts): void
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
    public function hydrateLikes($posts): void
    {
        // Get the posts ids
        $postsIds = array_unique(array_map(function ($post) {
            return $post->getId();
        }, $posts));

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

    /**
     * Hydrate the favorites of the posts.
     *
     * @param Post[] $posts
     */
    public function hydrateFavorites($posts): void
    {
        // Get the posts ids
        $postsIds = array_unique(array_map(function ($post) {
            return $post->getId();
        }, $posts));

        // Get the posts with their favorites
        /** @var Post[] $postWithFavorites */
        $postWithFavorites = $this->createQueryBuilder('p')
            ->select('p', 'f')
            ->join('p.favorites', 'f')
            ->where('p.id IN (:posts_ids)')
            ->setParameter('posts_ids', $postsIds)
            ->getQuery()
            ->getResult();

        // Create an array with the post id as key and an array of favorites as value
        $favoritesByPostId = [];
        foreach ($postWithFavorites as $post) {
            foreach ($post->getFavorites() as $favorite) {
                $favoritesByPostId[$post->getId()][] = $favorite;
            }
        }

        // Set the favorites of each post
        foreach ($posts as $post) {
            $post->setFavorites($favoritesByPostId[$post->getId()] ?? []);
        }
    }
}
