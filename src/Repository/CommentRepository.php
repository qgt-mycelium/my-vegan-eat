<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /** ---------- Custom queries ---------- */

    /**
     * Find comments from post.
     *
     * @return Comment[]
     */
    public function findCommentsFromPost(Post $post): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c', 'l')
            ->leftJoin('c.likes', 'l')
            ->where('c.isPublished = :isPublished')
            ->andWhere('c.isDeleted = :isDeleted')
            ->andWhere('c.post = :post')
            ->setParameters([
                ':isPublished' => true,
                ':isDeleted' => false,
                ':post' => $post,
            ])
            ->orderBy('c.createdAt', 'DESC');

        /** @var Comment[] $comments */
        $comments = $query->getQuery()->getResult();

        $this->hydrateComments($comments);

        $comments = array_filter($comments, function ($comment) {
            return null === $comment->getParent();
        });

        return $comments;
    }

    /**
     * Find number of comments per month for the last 12 months.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function findNumberOfCommentsPerMonthForLast12Months()
    {
        $query = $this->getEntityManager()
            ->getConnection()
            ->executeQuery(
                (string) preg_replace(
                    "/\s+/",
                    ' ',
                    'SELECT MONTH(created_at) as position, COUNT(id) as count, is_published, is_deleted
                    FROM comment 
                    WHERE created_at > SUBDATE(NOW(), INTERVAL 12 MONTH)
                    GROUP BY MONTH(created_at), is_published, is_deleted;'
                ),
            );

        $result = $query->fetchAllAssociative();

        $return = [
            'published' => array_filter($result, function ($item) {
                return true == $item['is_published'] && false == $item['is_deleted'];
            }),
            'deleted' => array_filter($result, function ($item) {
                return true == $item['is_published'] && true == $item['is_deleted'];
            }),
            'waiting_approval' => array_filter($result, function ($item) {
                return false == $item['is_published'] && false == $item['is_deleted'];
            }),
        ];

        return $return;
    }

    /* ---------- Hydrate functions ---------- */

    /**
     * Hydrate the comments.
     *
     * @param Comment[] $comments
     */
    public function hydrateComments($comments): void
    {
        // Get the comments ids
        $commentsIds = array_unique(array_map(function (Comment $comment) {
            return $comment->getId();
        }, $comments));

        /** @var Comment[] $commentWithComments */
        $commentWithComments = $this->createQueryBuilder('c')
            ->select('c', 'ch')
            ->join('c.comments', 'ch')
            ->where('c.id IN (:comments_ids)')
            ->andWhere('ch.isPublished = :isPublished')
            ->andWhere('ch.isDeleted = :isDeleted')
            ->setParameters([
                ':comments_ids' => $commentsIds,
                ':isPublished' => true,
                ':isDeleted' => false,
            ])
            ->getQuery()
            ->getResult();

        // Create an array with the comment id as key and an array of comments as value
        $commentsByCommentId = [];
        foreach ($commentWithComments as $comment) {
            foreach ($comment->getComments() as $child) {
                $commentsByCommentId[$comment->getId()][] = $child;
            }
        }

        // Set the comments to the comments
        foreach ($comments as $comment) {
            $comment->setComments($commentsByCommentId[$comment->getId()] ?? []);
        }
    }

    /**
     * Hydrate the posts.
     *
     * @param Comment[] $comments
     */
    public function hydratePosts($comments): void
    {
        // Get the posts ids
        $postsIds = array_unique(array_map(function (Comment $comment) {
            return $comment->getPost()->getId();
        }, $comments));

        /** @var Post[] $posts */
        $posts = $this->getEntityManager()->getRepository(Post::class)
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.id IN (:posts_ids)')
            ->setParameters([
                ':posts_ids' => $postsIds,
            ])
            ->getQuery()
            ->getResult();

        // Create an array with the post id as key and the post as value
        $postsByPostId = [];
        foreach ($posts as $post) {
            $postsByPostId[$post->getId()] = $post;
        }

        // Set the post to the comments
        foreach ($comments as $comment) {
            $comment->setPost($postsByPostId[$comment->getPost()->getId()]);
        }
    }
}
