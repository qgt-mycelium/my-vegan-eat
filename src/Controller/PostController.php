<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_posts', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('pages/post/index.html.twig', [
            'posts' => $postRepository->findPublishedOrderedByNewest(),
        ]);
    }

    #[Route('/posts/{slug}', name: 'app_post', methods: ['GET'])]
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

    #[IsGranted('ROLE_USER')]
    #[Route('/posts/{slug}/like', name: 'app_post_like', methods: ['POST'], condition: 'request.isXmlHttpRequest()')]
    public function like(Post $post, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var \App\Entity\User */
        $user = $this->getUser();

        if ($post->isLikedByUser($user)) {
            $post->removeLike($user);
        } else {
            $post->addLike($user);
        }

        $entityManager->flush();

        return $this->json($post->getLikes()->count());
    }
}
