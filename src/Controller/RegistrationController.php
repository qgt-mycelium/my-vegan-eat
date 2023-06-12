<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        // If the user is logged in, deny access!
        if ($this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        // 1. Build the form
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        // 2. Handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if (
            $form->isSubmitted() &&
            $form->isValid() &&
            is_string($form->get('password')->getData())
        ) {
            // 3. Encode the password (you could also do this via Doctrine listener)
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            // 4. Save the User!
            $entityManager->persist($user);
            $entityManager->flush();

            // 5. Redirect to some other page (like home)
            $this->addFlash('success', 'Your account has been created! You can now log in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
