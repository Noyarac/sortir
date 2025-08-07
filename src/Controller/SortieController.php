<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\FiltreSortiesType;
use App\Form\SortieType;
use App\Security\Voter\SortieVoter;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/sortie")]
final class SortieController extends AbstractController
{

    public function __construct(private readonly SortieService $sortieService){}
    #[Route('/{id}', name: 'sortie_details', requirements: ["id" => "\d+"], methods: ["GET"])]
    public function details(Sortie $sortie): Response
    {
        return $this->render('sortie/details.html.twig', [
            "sortie" => $sortie,
        ]);
    }
    #[Route('/creation', name: 'sortie_creation', requirements: ["id" => "\d+"], methods: ["GET","POST"])]
    public function creationSortie(Request $request): Response
    {
        $sortie = new Sortie();
        //Inutile de vérifier que $user existe car application entièrement protégée et seulement accessible à ROLE_USER
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
            $this->sortieService->gererEtatSortie($sortie, $etat);

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
    public function modificationSortie(Sortie $sortie, Request $request): Response
    {

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $etat = $request->request->get('action');
            $this->sortieService->gererEtatSortie($sortie, $etat);

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


        $this->sortieService->gererEtatSortie($sortie, Etat::ANNULEE->value);
        $this->addFlash('success', 'Cette sortie a bien été annulée.');

        return $this->redirectToRoute('main_home');
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
    public function inscription(Sortie $sortie, UserInterface $user, EntityManagerInterface $em, Request $request): Response
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

        $sortie->addParticipant($user);
        $em->persist($sortie);
        $em->flush();
        $this->addFlash("success", "Vous vous êtes inscrit(e) avec succès.");
        return $this->redirectToRoute("main_home");
    }

    #[Route('/{id}/desistement', name: 'sortie_desistement', requirements: ["id" => "\d+"], methods: ["POST"])]
    public function desistement(Sortie $sortie, UserInterface $user, EntityManagerInterface $em, Request $request): Response
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
        $sortie->removeParticipant($user);
        $em->persist($sortie);
        $em->flush();
        $this->addFlash("success", "Vous vous êtes désisté(e) avec succès.");
        return $this->redirectToRoute("main_home");
    }

}
