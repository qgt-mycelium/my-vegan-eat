<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'app_categories', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        /** @var Category[] $sub_categories */
        $sub_categories = $categoryRepository->findBy(['parent' => null]);
        $categoryRepository->hydratePosts($sub_categories);

        return $this->render('pages/category/index.html.twig', [
            'sub_categories' => $sub_categories,
        ]);
    }

    #[Route('/categories/{slug}', name: 'app_category', methods: ['GET'], requirements: ['slug' => '.+'])]
    public function category(CategoryRepository $categoryRepository, string $slug): Response
    {
        $category = $categoryRepository->findWithPosts($slug);
        $sub_categories = $categoryRepository->findAllChildCategories($slug);

        if (!$category instanceof Category) {
            throw $this->createNotFoundException('No category found for slug '.$slug);
        }

        $banner = [
            'title' => $category->getName(),
            'slogan' => $category->getDescription(),
        ];

        return $this->render('pages/category/index.html.twig', [
            'banner' => $banner,
            'category' => $category,
            'sub_categories' => $sub_categories,
        ]);
    }
}
