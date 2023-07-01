<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Profile\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Security\ChangePasswordType;
use App\Form\Security\Model\ChangePassword;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        // 1. Build all the forms
        $form_account = $this->createForm(ProfileType::class, $this->getUser());
        $form_password = $this->createForm(ChangePasswordType::class, new ChangePassword(), ['current_password_is_required' => true]);

        // 2. Handle the submit (will only happen on POST)
        $form_account->handleRequest($request);
        $form_password->handleRequest($request);

        if ($form_account->isSubmitted() && $form_account->isValid()) {
            // 3. Save the User!
            $entityManager->flush();
            // 4. Add a "flash" success message
            $this->addFlash('success', 'Your profile has been updated!');
            // 5. Redirect to the profile page
            return $this->redirectToRoute('app_profile');
        }

        if ($form_password->isSubmitted() && $form_password->isValid()) {
            // 3. Get the current user
            $user = $this->getUser();
            if ($user instanceof User && is_string($form_password->get('newPassword')->getData())) {
                // 4. Set the new encoded password
                $user->setPassword($passwordHasher->hashPassword(
                    $user,
                    $form_password->get('newPassword')->getData()
                ));
                // 4. Save the User!
                $entityManager->persist($user);
                $entityManager->flush();
                // 5. Add a "flash" success message
                $this->addFlash('success', 'Your password has been updated!');
            } else {
                // 4. Add a "flash" error message
                $this->addFlash('error', 'Something went wrong!');
            }
            // 6. Redirect to the profile page
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('pages/profile/index.html.twig', [
            'form_account' => $form_account->createView(),
            'form_password' => $form_password->createView(),
        ]);
    }
}
