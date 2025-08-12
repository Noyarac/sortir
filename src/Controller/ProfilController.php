<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil')]
final class ProfilController extends AbstractController
{
    #[Route('/modifier', name: 'profil_modifier', methods: ['GET', 'POST'])]
    public function modifierProfil(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        //Inutile de vérifier que $user existe car application entièrement protégée et seulement accessible à ROLE_USER
        $userForm = $this->createForm(UserType::class, $user, [
            'isAdmin' => false, //un utilisateur ne peut pas modifier son campus de référence, ni activer/désactiver son compte
        ]);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $plainPassword = $user->getPlainPassword();
            if($plainPassword){
                /** @var User $user */
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $user->setPlainPassword(null);
            }

            /** @var UploadedFile $image */
            $imageFile = $userForm->get('image')->getData();
            if ($imageFile) {
                try {
                    $imageFile->move(
                        $this->getParameter('app.backendProfilePicturesDirectory'),
                        $slugger->slug($user->getId()));
                } catch (FileException $e) {
                    $this->addFlash('danger', "Un problème est survenu lors de l'enregistrement de votre image de profil.");
                }
            }

            if ($userForm->get("deleteImage")->getViewData()) {
                $fileSystem = new Filesystem();
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

            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès!');
            return $this->redirectToRoute('main_home');
        }

        return $this->render('profil/creation-modificationProfil.html.twig', [
            'userForm' => $userForm,
            'user' => $user,
        ]);
    }
    #[Route('/{id}', name: 'profil_afficher', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function afficherProfil(User $user): Response
    {
        return $this->render('profil/afficherProfil.html.twig', [
            'user' => $user,
        ]);
    }
}
