<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Form\DTO\FiltreCampus;
use App\Form\FiltreCampusType;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
final class CampusController extends AbstractController
{
    #[Route('/admin/campus', name: 'admin_campus_list')]
    public function adminCampusList(Request $request, CampusRepository $campusRepository, EntityManagerInterface $em): Response
    {
        // Création d'un nouveau Campus
        $newCampus = new Campus;
        $newCampusForm = $this->createForm(CampusType::class, $newCampus);
        $newCampusForm->handleRequest($request);
        if ($newCampusForm->isSubmitted() && $newCampusForm->isValid()) {
            $em->persist($newCampus);
            $em->flush();
            $this->addFlash("success", $newCampus->getNom() . " a bien été ajouté.");
            $this->redirectToRoute("admin_campus_list");
        }

        // Filtrer les campus à afficher
        $session = $request->getSession();
        $filtreCampus = $session->get("filtreCampus") ?: new FiltreCampus();

        $filtreForm = $this->createForm(FiltreCampusType::class, $filtreCampus);
        $filtreForm->handleRequest($request);

        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $session->set("filtreCampus", $filtreCampus);
        }

        $allCampus = $campusRepository->findByFilter($filtreCampus);

        return $this->render(
            'admin/gestionCampus.html.twig',
            compact("allCampus", "filtreForm", "newCampusForm")
        );
    }

    #[Route('/admin/campus/{id}/supprimer', name: 'admin_campus_supprimer', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function adminCampusSupprimer(Campus $campus, Request $request, SortieRepository $sortieRepository, EntityManagerInterface $em): Response
    {
        // Vérification token CSRF
        $submittedToken = $request->getPayload()->get('_token');
        if (!$this->isCsrfTokenValid('suppression_campus_' . $campus->getId(), $submittedToken)) {
            $this->addFlash("danger", "Le token CSRF n'est pas valide.");
            return $this->redirectToRoute("admin_campus_list");
        }

        // Vérification de l'existence de sorties reliées au campus
        if (sizeof($sortieRepository->findBy(["campus" => $campus]))) {
            $this->addFlash("info", "Vous ne pouvez pas supprimer ce campus ; des sorties en dépendent.");
            return $this->redirectToRoute("admin_campus_list");
        }

        $em->remove($campus);
        $em->flush();
        $this->addFlash('success', $campus->getNom(). ' a bien été supprimé');

        return $this->redirectToRoute("admin_campus_list");
    }

}
