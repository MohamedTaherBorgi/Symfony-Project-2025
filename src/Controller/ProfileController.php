<?php

namespace App\Controller;

use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class ProfileController extends AbstractController
{
    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newPassword = $form->get('plainPassword')->getData();
            if ($newPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $newPassword)
                );
            }

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $filename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('user_images'), $filename);
                $user->setImage($filename);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully');

            return $this->redirectToRoute('app_profile_edit');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ): Response {

        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException();
        }

        if (!$this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        // 1. Log the user OUT before deleting the entity
        $tokenStorage->setToken(null);
        $session->invalidate();

        // 2. Remove the user from database
        $em->remove($user);
        $em->flush();

        $this->addFlash('danger', 'Your account has been deleted');

        // 3. Redirect anywhere (user is now fully logged out)
        return $this->redirectToRoute('app_home');
    }
}
