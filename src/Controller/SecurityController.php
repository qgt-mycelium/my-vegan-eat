<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // If the user is logged in, deny access!
        if ($this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('pages/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall
    }

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

        return $this->render('pages/security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
