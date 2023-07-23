<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        /** @var Comment[] $comments */
        $comments = $commentRepository->findBy(['isPublished' => false, 'isDeleted' => false], ['createdAt' => 'DESC']);
        $commentRepository->hydratePosts($comments);

        return $this->render('pages/dashboard/index.html.twig', [
            'comments' => $comments,
        ]);
    }
}
