<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Form\DTO\FiltreCampus;
use App\Form\FiltreCampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CampusController extends AbstractController
{
    #[IsGranted("ROLE_ADMIN")]
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
}
