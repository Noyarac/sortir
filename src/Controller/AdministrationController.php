<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Ville;
use App\Form\DTO\FiltreVille;
use App\Form\FiltreVilleType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/admin')]
final class AdministrationController extends AbstractController
{
    #[Route('/', name: 'admin_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('admin/home.html.twig', [
        ]);
    }

    #[Route('/campus', name: 'admin_campus', methods: ['GET', 'POST'])]
    public function gestionCampus(): Response
    {
        return $this->render('admin/gestionCampus.html.twig', []);
    }

    #[Route('/villes', name: 'admin_villes', methods: ['GET', 'POST'])]
    public function gestionVilles(Request $request, EntityManagerInterface $entityManager): Response
    {
        $filtreVille = new filtreVille();
        /** @var VilleRepository $villeRepository */
        $filtreVilleForm = $this->createForm(FiltreVilleType::class, $filtreVille);

        $villeRepository = $entityManager->getRepository(Ville::class);
        $villes = $villeRepository->findBy([], ['nom' => 'ASC']);

        $filtreVilleForm->handleRequest($request);
        if ($filtreVilleForm->isSubmitted() && $filtreVilleForm->isValid()) {
            $nomContient = $filtreVilleForm->get('nomContient')->getData();
            $villes = $villeRepository->findVillesByFilters($nomContient);
            return $this->render('admin/gestionVilles.html.twig', [
                "filtreVilleForm" => $filtreVilleForm,
                "villes" => $villes,
            ]);
        }

        return $this->render('admin/gestionVilles.html.twig', [
            "filtreVilleForm" => $filtreVilleForm,
            "villes" => $villes,
        ]);
    }

    #[Route('/utilisateurs/liste', name: 'admin_listeUtilisateurs', methods: ['GET', 'POST'])]
    public function listeUtilisateurs(EntityManagerInterface $entityManager): Response
    {
        /** @var UserRepository $userRepository */
        $userRepository = $entityManager->getRepository(User::class);
        $listeUtilisateurs = $userRepository->getListeUtilisateurs();
        return $this->render('admin/listeUtilisateurs.html.twig', [
            'listeUtilisateurs' => $listeUtilisateurs,
        ]);
    }

    #[Route('/utilisateurs/{id}/modifier', name: 'admin_modifierProfilUtilisateurs', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function modifierProfilUtilisateur(User $user, EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $userForm = $this->createForm(UserType::class, $user, [
            'isAdmin' => true, //un admin peut modifier le campus et activer/inactiver un compte mais ne peut pas modifier le mot de passe d'un utilisateur
        ]);

        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()){
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès!');
            return $this->redirectToRoute('admin_listeUtilisateurs');
        }

        return $this->render('profil/creation-modificationProfil.html.twig', [
            'userForm' => $userForm,
        ]);
    }

    #[Route('/utilisateurs/creer', name: 'admin_creerUtilisateur', methods: ['GET', 'POST'])]
    public function creerUtilisateur(Request $request, EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user, [
            'isAdmin' => true,
            'creation'=> true,
        ]);

        $userForm->handleRequest($request);
        if($userForm->isSubmitted() && $userForm->isValid()){
            $plainPassword = $userForm->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Nouvel utilisateur créé avec succès!');
            return $this->redirectToRoute('admin_listeUtilisateurs');
        }

        return $this->render('profil/creation-modificationProfil.html.twig', [
            'userForm' => $userForm,
        ]);
    }

}
