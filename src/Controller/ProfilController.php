<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/profil')]
final class ProfilController extends AbstractController
{
    #[Route('/modifier', name: 'profil_modifier', methods: ['GET','POST'])]
    public function modifierProfil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        //Inutile de vérifier que $user existe car application entièrement protégée et seulement accessible à ROLE_USER
        $userForm = $this->createForm(UserType::class, $user, [
            'campusModifiable' => false, //un utilisateur ne peut pas modifier son campus de référence
        ]);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès!');
            return $this->redirectToRoute('main_home');
        }

        return $this->render('profil/modifierProfil.html.twig', [
            'userForm' => $userForm,
        ]);
    }
    #[Route('/{id}', name: 'profil_afficher', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function afficherProfil(User $user): Response
    {
        return $this->render('profil/afficherProfil.html.twig', [
            'user'=>$user,
        ]);
    }
}
