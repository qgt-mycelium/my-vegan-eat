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
        // Get the posts limited to 8
        $posts = $postRepository->findPublishedOrderedByNewest(8);

        return $this->render('pages/home.html.twig', compact('posts'));
    }
}
