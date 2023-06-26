<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_posts')]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('pages/post/index.html.twig', [
            'posts' => $postRepository->findPublishedOrderedByNewest(),
        ]);
    }

    #[Route('/posts/{slug}', name: 'app_post')]
    public function show(Post $post): Response
    {
        return $this->render('pages/post/show.html.twig', [
            'post' => $post,
            'banner' => [
                'title' => $post->getTitle(),
                'slogan' => '',
            ],
        ]);
    }
}
