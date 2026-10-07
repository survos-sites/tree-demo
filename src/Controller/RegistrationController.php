<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Inventory;
use Symfony\Bundle\SecurityBundle\Security;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Inventory $inventory, Security $security): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_inventory');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->wrapInTransaction(function () use ($entityManager, $user, $inventory): void {
                $entityManager->persist($user);
                $entityManager->flush();
                $inventory->forUser($user);
            });
            return $security->login($user, 'form_login', 'main') ?? $this->redirectToRoute('app_inventory');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
