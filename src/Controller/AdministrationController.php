<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/administration')]
final class AdministrationController extends AbstractController
{
    #[Route('/', name: 'administration_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('administration/home.html.twig', [
        ]);
    }

    #[Route('/campus', name: 'administration_campus', methods: ['GET', 'POST'])]
    public function gestionCampus(): Response
    {
        return $this->render('administration/gestionCampus.html.twig', []);
    }

    #[Route('/villes', name: 'administration_villes', methods: ['GET', 'POST'])]
    public function gestionVilles(): Response
    {
        return $this->render('administration/gestionVilles.html.twig', []);
    }

    #[Route('/utilisateurs/liste', name: 'administration_listeUtilisateurs', methods: ['GET', 'POST'])]
    public function listeUtilisateurs(EntityManagerInterface $entityManager): Response
    {
        /** @var UserRepository $userRepository */
        $userRepository = $entityManager->getRepository(User::class);
        $listeUtilisateurs = $userRepository->getListeUtilisateurs();
        return $this->render('administration/listeUtilisateurs.html.twig', [
            'listeUtilisateurs' => $listeUtilisateurs,
        ]);
    }

    #[Route('/utilisateurs/{id}/modifier', name: 'administration_modifierProfilUtilisateurs', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function modifierProfilUtilisateurs(User $user, EntityManagerInterface $entityManager): Response
    {

        return $this->render('administration/listeUtilisateurs.html.twig', [
            'listeUtilisateurs' => $listeUtilisateurs,
        ]);
    }

}
