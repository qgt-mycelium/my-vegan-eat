<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /** ---------- Custom queries ---------- */

    /**
     * Find category with their posts.
     *
     * @return Category
     */
    public function findWithPosts(string $categorySlug): ?Category
    {
        $query = $this->createQueryBuilder('c')
            ->select('c', 'p')
            ->leftJoin('c.posts', 'p')
            ->where('c.slug = :slug')
            ->setParameter('slug', $categorySlug)
            ->getQuery();

        /** @var Category|null $category */
        $category = $query->getOneOrNullResult();

        if ($category instanceof Category) {
            $posts = $category->getPosts()->toArray();
            $postRepository = $this->getEntityManager()->getRepository(Post::class);

            $postRepository->hydrateTags($posts);
            $postRepository->hydrateLikes($posts);
            $postRepository->hydrateFavorites($posts);
        }

        return $category;
    }

    /**
     * Find all child categories of a given category slug.
     *
     * @return Category[]
     */
    public function findAllChildCategories(string $categorySlug): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.parent', 'cp')
            ->where('cp.slug = :slug')
            ->setParameter('slug', $categorySlug)
            ->getQuery();

        /** @var Category[] $categories */
        $categories = $query->getResult();

        return $categories;
    }
}
