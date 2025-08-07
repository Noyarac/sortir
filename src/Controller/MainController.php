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
        // Filtre : valeurs par défaut
        $filtreSortie = new FiltreSortie();
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $filtreSortie->setUser($user);
        $filtreSortie->setCampus($user->getCampus());

        // Filtre : Ecrasement si cookie présent
        $filtreCookie = $request->cookies->get("filtreSortie");
        if ($filtreCookie) {
            $filtreSortie = unserialize($filtreCookie);
        }

        // Rechargement de l'objet Campus
        $filtreSortie->setCampus($campusRepository->find($filtreSortie->getCampus()->getId()));


        $filtreForm = $this->createForm(FiltreSortieType::class, $filtreSortie);
        $filtreForm->handleRequest($request);

        // if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
        // }

        $sorties = $sortieRepository->findByFilter($filtreSortie);
        $response = $this->render('main/home.html.twig', [
            "filtreForm" => $filtreForm,
            "sorties" => $sorties,
        ]);

        $cookie = new Cookie("filtreSortie", serialize($filtreSortie), strtotime('+1 day'));
        $response->headers->setCookie($cookie);
        return $response;
    }
}
