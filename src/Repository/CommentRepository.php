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
            ->select('c')
            ->where('c.isPublished = :isPublished')
            ->andWhere('c.isDeleted = :isDeleted')
            ->andWhere('c.post = :post')
            ->setParameters([
                ':isPublished' => true,
                ':isDeleted' => false,
                ':post' => $post
            ])
            ->orderBy('c.createdAt', 'DESC')
        ;

        /** @var Comment[] $comments */
        $comments = $query->getQuery()->getResult();

        $this->hydrateComments($comments);

        $comments = array_filter($comments, function ($comment) {
            return $comment->getParent() === null;
        });

        return $comments;
    }

     /* ---------- Hydrate functions ---------- */

    /**
     * Hydrate the posts of the given categories.
     *
     * @param Comment[] $comments
     */
    public function hydrateComments($comments): void
    {
        // Get the comments ids
        $commentsIds = array_map(function (Comment $comment) {
            return $comment->getId();
        }, $comments);

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
                ':isDeleted' => false
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
}
