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

        // Filtre : Récupération si existence du cookie
        $filtreCookie = $request->cookies->get("filtreSortie");
        if ($filtreCookie) {
            $filtreSortie = unserialize($filtreCookie);
        } else {
        // Filtre : valeurs par défaut
            $filtreSortie = new FiltreSortie();
            $filtreSortie->setUser($user);
            $filtreSortie->setCampus($user->getCampus());
        }

        // Rechargement de l'objet Campus
        $filtreSortie->setCampus($campusRepository->find($filtreSortie->getCampus()->getId()));

        // Creation du formulaire de filtre
        $filtreForm = $this->createForm(FiltreSortieType::class, $filtreSortie, ['csrf_protection' => false]);
        $filtreForm->handleRequest($request);

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
