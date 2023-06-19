<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('pages/home.html.twig', [
            'posts' => $postRepository->findPublishedOrderedByNewest(8),
        ]);
    }

    /**
     * Fragment caching for footer.
     */
    public function footer(PostRepository $postRepository): Response
    {
        return $this->render('partials/_footer.html.twig', [
            'posts' => $postRepository->findPopularOrderedByMostLiked(4),
        ])->setSharedMaxAge(3600);
    }
}
