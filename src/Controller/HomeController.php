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
        $posts = $postRepository->findPublishedOrderedByNewest(8);
        $goodToKnow = $postRepository->findPublishedGoodToKnowOrderedByNewest();
        $hydratablePosts = array_merge($posts, $goodToKnow);

        $postRepository->hydrateTags($hydratablePosts);
        $postRepository->hydrateLikes($hydratablePosts);
        $postRepository->hydrateFavorites($hydratablePosts);

        return $this->render('pages/home.html.twig', [
            'posts' => $posts,
            'good_to_know' => $goodToKnow,
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
