<?php

namespace App\Controller;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class ProfilController extends AbstractController
{
    #[Route('/profil/modifier', name: 'profil_modifier', methods: ['GET','POST'])]
    public function modifierProfil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $userForm = $this->createForm(UserType::class, $user, [
            'campusModifiable' => false,
        ]);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('main_home');
        }

        return $this->render('profil/modifierProfil.html.twig', [
            'userForm' => $userForm,
        ]);
    }
}
