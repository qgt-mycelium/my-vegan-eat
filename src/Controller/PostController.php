<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_posts', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('pages/post/index.html.twig', [
            'posts' => $postRepository->findPublishedOrderedByNewest(),
        ]);
    }

    #[Route('/posts/{slug}', name: 'app_post', methods: ['GET', 'POST'])]
    public function show(Post $post, CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = (new Comment());
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $user = $this->getUser();
                if ($user instanceof User) {
                    $comment->setAuthor($user);
                    $comment->setPost($post);
                    $entityManager->persist($comment);
                    $entityManager->flush();
                    $this->addFlash('success', 'Your comment has been saved. It will be subject to moderation as soon as possible.');

                    return $this->redirectToRoute('app_post', ['slug' => $post->getSlug()]);
                } else {
                    $this->addFlash('error', 'You must be logged in to post comments');
                }
            } else {
                $this->addFlash('error', 'Your comment could not be saved. Please check the form.');
            }
        }

        return $this->render('pages/post/show.html.twig', [
            'post' => $post,
            'banner' => [
                'title' => $post->getTitle(),
                'slogan' => '',
            ],
            'comments' => $commentRepository->findCommentsFromPost($post),
            'form' => $form->createView(),
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

    #[IsGranted('ROLE_USER')]
    #[Route('/comment/{id}/reply', name: 'app_post_comment_reply', methods: ['GET', 'POST'], condition: "context.getMethod() in ['GET'] and request.isXmlHttpRequest() or context.getMethod() in ['POST']")]
    public function replyComment(Comment $comment, Request $request, EntityManagerInterface $entityManager): JsonResponse|Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $reply = (new Comment())
            ->setParent($comment)
            ->setPost($comment->getPost())
            ->setAuthor($user);

        $form = $this->createForm(CommentType::class, $reply, [
            'action' => $this->generateUrl('app_post_comment_reply', ['id' => $comment->getId()]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($reply);
                $entityManager->flush();
                $this->addFlash('success', 'Your comment has been saved. It will be subject to moderation as soon as possible.');
            } else {
                $this->addFlash('error', 'Your comment could not be saved. Please check the form.');
            }

            return $this->redirectToRoute('app_post', ['slug' => $comment->getPost()->getSlug()]);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'form' => $this->renderView('pages/post/_reply_form.html.twig', [
                    'form' => $form->createView(),
                ]),
            ]);
        }

        return new JsonResponse(['message' => 'Method not allowed'], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/comment/{id}/delete', name: 'app_post_comment_delete', methods: ['POST'])]
    public function deleteComment(Comment $comment, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($comment->getAuthor() !== $this->getUser()) {
            $this->addFlash('error', 'You are not allowed to delete this comment');

            return $this->redirectToRoute('app_post', ['slug' => $comment->getPost()->getSlug()]);
        }

        if ($this->isCsrfTokenValid('delete'.$comment->getId(), (string) $request->request->get('_token'))) {
            $comment->setIsDeleted(true);
            $entityManager->flush();
            $this->addFlash('success', 'Your comment has been successfully deleted');
        } else {
            $this->addFlash('error', 'Your comment could not be deleted');
        }

        return $this->redirectToRoute('app_post', ['slug' => $comment->getPost()->getSlug()]);
    }
}
