<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\DTO\FiltreVille;
use App\Form\FiltreVilleType;
use App\Form\UserImportType;
use App\Form\UserType;
use App\Form\VilleType;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use App\Service\UserImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
final class AdministrationController extends AbstractController
{
    #[Route('/', name: 'admin_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('admin/home.html.twig', [
        ]);
    }

    #[Route('/villes', name: 'admin_gestionVilles', methods: ['GET', 'POST'])]
    public function gestionVilles(Request $request, EntityManagerInterface $entityManager): Response
    {
        $villeRepository = $entityManager->getRepository(Ville::class);

        //On remonte de la BDD toutes les villes
        $villes = $villeRepository->findBy([], ['nom' => 'ASC']);

        //Formulaire de création d'une nouvelle ville
        $ville= new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();
            $this->addFlash('success','Ville créée avec succès!');
            return $this->redirectToRoute('admin_gestionVilles');
        }

        //Formulaire de filtre
        $filtreVille = new filtreVille();
        /** @var VilleRepository $villeRepository */
        $filtreVilleForm = $this->createForm(FiltreVilleType::class, $filtreVille);
        $filtreVilleForm->handleRequest($request);

        if ($filtreVilleForm->isSubmitted() && $filtreVilleForm->isValid()) {
            $nomContient = $filtreVilleForm->get('nomContient')->getData();
            $villes = $villeRepository->findVillesByFilters($nomContient);
            return $this->render('admin/gestionVilles.html.twig', [
                "filtreVilleForm" => $filtreVilleForm,
                "villes" => $villes,
                "villeForm" => $villeForm,
            ]);
        }

        return $this->render('admin/gestionVilles.html.twig', [
            "filtreVilleForm" => $filtreVilleForm,
            "villes" => $villes,
            "villeForm" => $villeForm,
        ]);
    }

    #[Route('/villes/{id}/modifier', name: 'admin_modifierVille', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function modifierVille(Ville $ville, Request $request, EntityManagerInterface $entityManager): Response
    {
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Ville mise à jour');
            return $this->redirectToRoute('admin_villes');
        }

        return $this->render('admin/modifierVille.html.twig', [
            "villeForm" => $villeForm,
        ]);
    }

    #[Route('/villes/{id}/supprimer', name: 'admin_supprimerVille', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function supprimerVille(Ville $ville, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Vérifier le token CSRF
        $tokenIsValid = $this->isCsrfTokenValid('suppression_ville_' . $ville->getId(), $request->request->get('_token'));
        if (!$tokenIsValid) {
            $this->addFlash('danger', "Cette ville n'a pas pu être supprimée, jeton CSRF invalide");
            return $this->redirectToRoute('admin_villes');
        }

        //Vérification absence référencement de la ville dans une sortie
        /** @var SortieRepository $sortieRepository */
        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $nbSortiesUtilisantVille = $sortieRepository->countByVille($ville);
        if ($nbSortiesUtilisantVille > 0) {
            $this->addFlash("danger", "Cette ville ne peut pas être supprimée, elle est référencée dans une ou plusieurs sorties.");
            return $this->redirectToRoute('admin_villes');
        }


        $entityManager->remove($ville);
        $entityManager->flush();
        $this->addFlash('success', 'La ville '.$ville->getNom(). ' a bien été supprimée');
        return $this->redirectToRoute('admin_villes');
    }

    #[Route('/utilisateurs', name: 'admin_gestionUtilisateurs', methods: ['GET', 'POST'])]
    public function gestionUtilisateurs(EntityManagerInterface $entityManager, Request $request, UserImportService $userImportService): Response
    {
        /** @var UserRepository $userRepository */
        $userRepository = $entityManager->getRepository(User::class);
        $listeUtilisateurs = $userRepository->getListeUtilisateurs();

        $userImportForm = $this->createForm(UserImportType::class);
        $userImportForm->handleRequest($request);

        if($userImportForm->isSubmitted() && $userImportForm->isValid()){
            //Le fichier doit contenir les infos suivantes : campus, nom, prenom, email
            $csvFile = $userImportForm->get('csvFile')->getData();
            //fichier transmis au service pour traitement (création utilisateurs)
            try{
                $nbUtilisateursCrees = $userImportService->importFromFile($csvFile->getPathname());
                $message = $nbUtilisateursCrees == 1 ? "Un utilisateur a été créé" : "{$nbUtilisateursCrees} utilisateurs ont été créés";
                $this->addFlash('success', $message);
                return $this->redirectToRoute('admin_gestionUtilisateurs');
            } catch (\RuntimeException $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('admin_gestionUtilisateurs');
            } catch (\Exception $e) {
                $this->addFlash('danger', "Une erreur technique est survenue lors de l'import. Veuillez réésayer plus tard");
                return $this->redirectToRoute('admin_gestionUtilisateurs');
            }
        }

        return $this->render('admin/gestionUtilisateurs.html.twig', [
            'listeUtilisateurs' => $listeUtilisateurs,
            'userImportForm' => $userImportForm,
        ]);
    }

    #[Route('/utilisateurs/{id}/modifier', name: 'admin_modifierProfilUtilisateurs', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function modifierProfilUtilisateur(User $user, EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger): Response
    {
        $userForm = $this->createForm(UserType::class, $user, [
            'isAdmin' => true, //un admin peut modifier le campus et activer/inactiver un compte mais ne peut pas modifier le mot de passe d'un utilisateur
        ]);

        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()){
            if ($userForm->get("deleteImage")->getViewData()) {
                $fileSystem = new Filesystem();
                $filePath = $this->getParameter('app.backendProfilePicturesDirectory') . '/' . $user->getId();
                if ($fileSystem->exists($filePath)) {
                    try {
                        $fileSystem->remove($filePath);
                    } catch (IOExceptionInterface $exception) {
                        $this->addFlash("danger", "Impossible de supprimer l'image de profile.");
                    }
                } else {
                    $this->addFlash("info", "L'image de profile n'a pas été supprimée, car elle n'existe pas.");
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès!');
            return $this->redirectToRoute('admin_gestionUtilisateurs');
        }
        return $this->render('profil/creation-modificationProfil.html.twig', [
            'userForm' => $userForm,
            'user' => $user,
        ]);
    }

    #[Route('/utilisateurs/creer', name: 'admin_creerUtilisateur', methods: ['GET', 'POST'])]
    public function creerUtilisateur(Request $request, EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger): Response
    {
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user, [
            'isAdmin' => true,
            'creation'=> true,
        ]);

        $userForm->handleRequest($request);
        if($userForm->isSubmitted() && $userForm->isValid()){
            $plainPassword = $user->getPlainPassword();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setPlainPassword(null);

            if ($userForm->get("deleteImage")->getViewData()) {
                $fileSystem = new Filesystem;
                $filePath = $this->getParameter('app.backendProfilePicturesDirectory') . '/' . $user->getId();
                if ($fileSystem->exists($filePath)) {
                    try {
                        $fileSystem->remove($filePath);
                    } catch (IOExceptionInterface $exception) {
                        $this->addFlash("danger", "Impossible de supprimer votre image de profile.");
                    }
                } else {
                    $this->addFlash("info", "Votre image de profile n'a pas été supprimée, car elle n'existe pas.");
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Nouvel utilisateur créé avec succès!');
            return $this->redirectToRoute('admin_gestionUtilisateurs');
        }

        return $this->render('profil/creation-modificationProfil.html.twig', [
            'userForm' => $userForm,
            "user" => $user,
        ]);
    }

    #[Route('/utilisateurs/{id}/toggle', name: 'admin_toggleCompteUtilisateur', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function toggleCompteUtilisateur(User $user, Request $request, EntityManagerInterface $entityManager) : Response
    {
        // Vérifier le token CSRF
        $tokenIsValid = $this->isCsrfTokenValid('toggle_compte_utilisateur_' . $user->getId(), $request->request->get('_token'));
        if (!$tokenIsValid) {
            $message = $user->isActif() ? "Ce compte n'a pas pu être désactivé, jeton CSRF invalide" : "Ce compte n'a pas pu être activé, jeton CSRF invalide";
            $this->addFlash('danger', $message);
            return $this->redirectToRoute('admin_gestionUtilisateurs');
        }

        $user->setActif(!$user->isActif());
        $entityManager->flush();

        $this->addFlash('success', "Le compte de ". $user->getPseudo()." a bien été ". ($user->isActif() ? 'activé' : 'désactivé').".");
        return $this->redirectToRoute('admin_gestionUtilisateurs');
    }

}
