<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(
        Request                     $request,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException();
        }

        $originalData = [
            'prenom' => $user->getPrenom(),
            'nom' => $user->getNom(),
            'email' => $user->getEmail(),
            'telephone' => $user->getTelephone(),
            'street' => $user->getStreet(),
            'city' => $user->getCity(),
            'postalCode' => $user->getPostalCode(),
        ];

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        $showModal = false;
        $newPasswordField = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $changed = false;
            foreach ($originalData as $field => $value) {
                if ($user->{'get' . ucfirst($field)}() !== $value) {
                    $changed = true;
                    break;
                }
            }

            $newPassword = $form->get('plainPassword')->getData();
            if ($newPassword) {
                $changed = true;
            }

            if ($request->request->has('currentPasswordConfirmed')) {
                $newPasswordField = $request->request->get('plainPasswordHidden');
                if ($newPasswordField) {
                    $user->setPassword($passwordHasher->hashPassword($user, $newPasswordField));
                }
            } elseif ($changed) {
                $showModal = true;
                $newPasswordField = $newPassword;
            }

            if (!$showModal) {
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $filename = uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move($this->getParameter('user_images'), $filename);
                    $user->setImage($filename);
                }

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Profile updated successfully');
                return $this->redirectToRoute('app_profile_edit');
            }
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form,
            'showCurrentPasswordModal' => $showModal,
            'newPasswordField' => $newPasswordField,
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(
        Request                   $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface    $entityManager,
        SessionInterface          $session,
        TokenStorageInterface     $tokenStorage
    ): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $submittedToken = $request->request->get('_token');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_account', $submittedToken))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_profile_edit');
        }

        // Delete the user
        $entityManager->remove($user);
        $entityManager->flush();

        // Log out the deleted user
        $tokenStorage->setToken(null);
        $session->invalidate();

        $this->addFlash('success', 'Your account has been deleted.');
        return $this->redirectToRoute('app_home');
    }
}
