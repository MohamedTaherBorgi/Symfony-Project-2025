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

        // Save original values
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
        $passwordError = null;
        $newPassword = null;

        if ($form->isSubmitted() && $form->isValid()) {

            // Detect changes
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

            // If user confirmed password → VERIFY IT
            if ($request->request->has('currentPasswordConfirmed')) {

                $currentPassword = $request->request->get('currentPasswordConfirmed');

                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    // ❌ WRONG PASSWORD
                    $showModal = true;
                    $passwordError = 'Incorrect current password.';
                } else {
                    // ✅ PASSWORD OK → APPLY CHANGES
                    if ($newPassword) {
                        $user->setPassword(
                            $passwordHasher->hashPassword($user, $newPassword)
                        );
                    }

                    $imageFile = $form->get('imageFile')->getData();
                    if ($imageFile) {
                        $filename = uniqid() . '.' . $imageFile->guessExtension();
                        $imageFile->move($this->getParameter('user_images'), $filename);
                        $user->setImage($filename);
                    }

                    $em->flush();
                    $this->addFlash('success', 'Profile updated successfully');
                    return $this->redirectToRoute('app_profile_edit');
                }

            } elseif ($changed) {
                // Ask for password confirmation
                $showModal = true;
            }
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form,
            'showCurrentPasswordModal' => $showModal,
            'passwordError' => $passwordError,
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(
        Request                   $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface    $em,
        SessionInterface          $session,
        TokenStorageInterface     $tokenStorage
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $submittedToken = $request->request->get('_token');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_account', $submittedToken))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_profile_edit');
        }

        /* ===============================
           1️⃣ DELETE USER ORDERS FIRST
           =============================== */
        $orders = $em->getRepository(\App\Entity\Order::class)
            ->findBy(['user' => $user]);

        foreach ($orders as $order) {
            $em->remove($order);
        }

        /* ===============================
           2️⃣ DELETE USER
           =============================== */
        $em->remove($user);
        $em->flush();

        /* ===============================
           3️⃣ LOGOUT + SESSION CLEANUP
           =============================== */
        $tokenStorage->setToken(null);
        $session->invalidate();

        return $this->redirectToRoute('app_home');
    }
}
