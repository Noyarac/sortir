<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DTO\FiltreSortie;
use App\Form\FiltreSortiesType;
use App\Form\FiltreSortieType;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ["GET", "POST"])]
    public function home(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Valeurs par défaut du filtre
        $filtreSortie = new FiltreSortie();
        $filtreSortie->setUser($user);
        $filtreSortie->setCampus($user->getCampus());

        // Ecrasement du filtre si présent dans la session
        $session = $request->getSession();
        $filtreSortie = $session->get("filtreSortie") ?: $filtreSortie;

        // Rechargement de l'objet Campus
        $filtreSortie->setCampus($campusRepository->find($filtreSortie->getCampus()->getId()));

        // Creation du formulaire de filtre
        $filtreForm = $this->createForm(FiltreSortieType::class, $filtreSortie);
        $filtreForm->handleRequest($request);

        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $session->set("filtreSortie", $filtreSortie);
            $sorties = $sortieRepository->findByFilter($filtreSortie);
            return $this->render('main/home.html.twig', [
                "filtreForm" => $filtreForm,
                "sorties" => $sorties,
            ]);
        }

        $sorties = $sortieRepository->findByFilter($filtreSortie);
        return $this->render('main/home.html.twig', [
            "filtreForm" => $filtreForm,
            "sorties" => $sorties,
        ]);
    }
}
