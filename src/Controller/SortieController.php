<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieAnnulationType;
use App\Form\SortieType;
use App\Security\Voter\SortieVoter;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/sortie")]
final class SortieController extends AbstractController
{
    #[Route('/{id}', name: 'sortie_details', requirements: ["id" => "\d+"], methods: ["GET"])]
    public function details(Sortie $sortie): Response
    {
        return $this->render('sortie/details.html.twig', [
            "sortie" => $sortie,
        ]);
    }

    #[Route('/creation', name: 'sortie_creation', requirements: ["id" => "\d+"], methods: ["GET","POST"])]
    public function creationSortie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        //Inutile de vérifier que $user existe car application entièrement protégée et seulement accessible à ROLE_USER
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $sortie->setOrganisateur($user);
        $sortie->setCampus($user->getCampus());
        //Préremplissage du formulaire pour amélioration UX utilisateur
        $sortie->setDateHeureDebut((new \DateTimeImmutable('+2 days'))->setTime(18,0),);
        $sortie->setDateLimiteInscription(new \DateTimeImmutable('+1 day'));


        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $etat = $request->request->get('action');
            $sortie->setEtat($etat);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $message = $etat === Etat::OUVERTE->value
                ? "Sortie créée avec succès!"
                : "Sortie modifiée avec succès! Attention, elle n'est pas encore publiée.";

            $this->addFlash("success", $message);
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/creation-modification.html.twig', [
            'sortieForm' => $sortieForm,
            'isModification' => false,
        ]);
    }

    #[Route('/{id}/modification', name: 'sortie_modification', requirements: ["id" => "\d+"], methods: ["GET","POST"])]
    #[IsGranted('sortie_modification', 'sortie')]
    public function modificationSortie(Sortie $sortie, Request $request, EntityManagerInterface $entityManager): Response
    {

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $etat = $request->request->get('action');
            $sortie->setEtat($etat);

            $entityManager->flush();

            $message = $etat === Etat::OUVERTE->value
                ? "Sortie publiée avec succès!"
                : "Sortie modifiée avec succès! Attention, elle n'est pas encore publiée.";

            $this->addFlash("success", $message);
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/creation-modification.html.twig', [
            'sortieForm' => $sortieForm,
            'sortie' => $sortie,
            'isModification' => true,
        ]);
    }

    #[Route('/{id}/publication', name: 'sortie_publication', requirements: ["id" => "\d+"], methods: ["POST"])]
    #[IsGranted('sortie_modification', 'sortie')]
    public function publicationSortie(Sortie $sortie, Request $request): Response
    {
        // Vérifier le token CSRF
        $tokenIsValid = $this->isCsrfTokenValid('publication_sortie_' . $sortie->getId(), $request->request->get('_token'));
        if (!$tokenIsValid) {
            $this->addFlash('danger', "Cette sortie n'a pas pu être publiée, jeton CSRF invalide");
            return $this->redirectToRoute('main_home');
        }

        $this->sortieService->gererEtatSortie($sortie, Etat::OUVERTE->value);
        $this->addFlash('success', 'Sortie publiée avec succès.');

        return $this->redirectToRoute('main_home');
    }

    #[Route('/{id}/annulation', name: 'sortie_annulation', requirements: ["id" => "\d+"], methods: ["GET","POST"])]
    #[IsGranted('sortie_annulation', 'sortie')]
    public function annulationSortie(Sortie $sortie, Request $request): Response
    {
        $sortieAnnulationForm = $this->createForm(SortieAnnulationType::class);
        $sortieAnnulationForm->handleRequest($request);

        if($sortieAnnulationForm->isSubmitted() && $sortieAnnulationForm->isValid()){
            $motifAnnulation = $sortieAnnulationForm->get('motif')->getData();
            $descriptionEtInfos = $sortie->getInfosSortie();
            $descriptionEtInfos .= "\n SORTIE ANNULÉE : ";
            $descriptionEtInfos .= $motifAnnulation;

            $sortie->setInfosSortie($descriptionEtInfos);

            $this->sortieService->gererEtatSortie($sortie, Etat::ANNULEE->value);
            $this->addFlash('success', 'Cette sortie a bien été annulée.');
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/annulation.html.twig', [
            'sortie' => $sortie,
            'sortieAnnulationForm' => $sortieAnnulationForm,
        ]);
    }

    #[Route('/{id}/suppression', name: 'sortie_suppression', requirements: ["id" => "\d+"], methods: ["POST"])]
    #[IsGranted('sortie_modification', 'sortie')]
    public function suppressionSortie(Sortie $sortie, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF
        $tokenIsValid = $this->isCsrfTokenValid('suppression_sortie_' . $sortie->getId(), $request->request->get('_token'));
        if (!$tokenIsValid) {
            $this->addFlash('danger', "Cette sortie n'a pas pu être supprimée, jeton CSRF invalide");
            return $this->redirectToRoute('main_home');
        }

        $entityManager->remove($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Sortie supprimée avec succès.');

        return $this->redirectToRoute('main_home');
    }

    #[Route('/{id}/inscription', name: 'sortie_inscription', requirements: ["id" => "\d+"], methods: ["POST"])]
    public function inscription(Request $request, Sortie $sortie, SortieService $sortieService, UserInterface $user): Response
    {
        if (!$this->isGranted(SortieVoter::INSCRIPTION, $sortie)) {
            $this->addFlash("danger", "Il n'est pas possible de s'inscrire à cette sortie");
            return $this->redirectToRoute("main_home");
        }
        // Vérifier le token CSRF
        $tokenIsValid = $this->isCsrfTokenValid('inscription_sortie_' . $sortie->getId(), $request->request->get('_token'));
        if (!$tokenIsValid) {
            $this->addFlash('danger', "Inscription impossible, jeton CSRF invalide");
            return $this->redirectToRoute('main_home');
        }

        if ($sortieService->inscription($sortie, $user)) {
            $this->addFlash("success", "Vous vous êtes inscrit(e) avec succès.");
        } else {
            $this->addFlash("danger", "Une erreur est survenue, votre inscription n'a pas été prise en compte.");
        }
        return $this->redirectToRoute("main_home");
    }

    #[Route('/{id}/desistement', name: 'sortie_desistement', requirements: ["id" => "\d+"], methods: ["POST"])]
    public function desistement(Request $request, Sortie $sortie, UserInterface $user, SortieService $sortieService): Response
    {
        if (!$this->isGranted(SortieVoter::DESISTEMENT, $sortie)) {
            $this->addFlash("danger", "Il n'est pas possible de se désister de cette sortie");
            return $this->redirectToRoute("main_home");
        }
        // Vérifier le token CSRF
        $tokenIsValid = $this->isCsrfTokenValid('desistement_sortie_' . $sortie->getId(), $request->request->get('_token'));
        if (!$tokenIsValid) {
            $this->addFlash('danger', "Désistement impossible, jeton CSRF invalide");
            return $this->redirectToRoute('main_home');
        }
                if ($sortieService->desistement($sortie, $user)) {
        $this->addFlash("success", "Vous vous êtes désisté(e) avec succès.");
        } else {
            $this->addFlash("danger", "Une erreur est survenue, votre désinscription n'a pas été prise en compte.");
        }

        return $this->redirectToRoute("main_home");
    }

}
