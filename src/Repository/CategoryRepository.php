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

        $this->hydratePosts($categories);

        // Remove categories without posts
        $categories = array_filter($categories, function (Category $category) {
            return count($category->getPosts()) > 0;
        });

        return $categories;
    }

    /* ---------- Hydrate functions ---------- */

    /**
     * Hydrate the posts of the given categories.
     *
     * @param Category[] $categories
     */
    public function hydratePosts(array $categories): void
    {
        // Get the categories ids
        $categoriesIds = array_map(function (Category $category) {
            return $category->getId();
        }, $categories);

        /** @var Category[] $categoryWithPosts */
        $categoryWithPosts = $this->createQueryBuilder('c')
            ->select('c', 'p')
            ->join('c.posts', 'p')
            ->where('c.id IN (:categories_ids)')
            ->setParameter('categories_ids', $categoriesIds)
            ->getQuery()
            ->getResult();

        // Create an array with the post id as key and an array of likes as value
        $postsByCategoryId = [];
        foreach ($categoryWithPosts as $category) {
            foreach ($category->getPosts() as $post) {
                $postsByCategoryId[$category->getId()][] = $post;
            }
        }

        // Set the posts to the categories
        foreach ($categories as $category) {
            $category->setPosts($postsByCategoryId[$category->getId()] ?? []);
        }
    }
}
