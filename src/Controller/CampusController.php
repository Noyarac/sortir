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

#[Route('/admin/campus')]
final class CampusController extends AbstractController
{
    #[Route('/', name: 'admin_campus_list')]
    public function adminCampusList(Request $request, CampusRepository $campusRepository, EntityManagerInterface $em): Response
    {
        // Formulaire nouveau Campus
        $newCampus = new Campus;
        $newCampusForm = $this->createForm(
            CampusType::class,
            $newCampus,
            [
                "action" => $this->generateUrl("admin_campus_creer"),
                "method" => "POST",
            ]
        );

        // Filtrer les campus à afficher
        $session = $request->getSession();
        $filtreCampus = $session->get("filtreCampus") ?: new FiltreCampus();

        $filtreForm = $this->createForm(FiltreCampusType::class, $filtreCampus);
        $filtreForm->handleRequest($request);

        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $session->set("filtreCampus", $filtreCampus);
        }

        $allCampus = $campusRepository->findByFilter($filtreCampus);
        $modifyCampusForms = array_combine(
            array_map(fn($campus) => "campus" . $campus->getId(), $allCampus),
            array_map(fn($campus) => $this->createForm(
                CampusType::class,
                $campus,
                [
                    "action" => $this->generateUrl("admin_campus_modifier", ["id" => $campus->getId()]),
                    "method" => "POST",
                ]
            )->createView(), $allCampus)
        );

        return $this->render(
            'admin/gestionCampus.html.twig',
            compact("allCampus", "filtreForm", "newCampusForm", "modifyCampusForms")
        );
    }

    #[Route('/creer', name: 'admin_campus_creer', methods: ['POST'])]
    public function adminCampusCreer(Request $request, EntityManagerInterface $em): Response
    {
        $campus = new Campus;
        $campusForm = $this->createForm(CampusType::class, $campus);
        $campusForm->handleRequest($request);
        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $em->persist($campus);
            $em->flush();
            $this->addFlash('success', $campus->getNom(). ' a bien été créé.');
        } else {
            $this->addFlash("danger", "Le campus n'a pas été créé.");
        }
        return $this->redirectToRoute("admin_campus_list");
    }

    #[Route('/{id}/supprimer', name: 'admin_campus_supprimer', requirements: ['id'=>'\d+'], methods: ['POST'])]
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

    #[Route('/{id}/modifier', name: 'admin_campus_modifier', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function adminCampusModifier(Campus $campus, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($campus);
            $em->flush();
            $this->addFlash('success', $campus->getNom(). ' a bien été modifié.');
        }

        return $this->redirectToRoute("admin_campus_list");
    }

}
