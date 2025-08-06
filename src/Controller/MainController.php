<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\FiltreSortiesType;
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
        $campus = null;
        $filtreCampusId = $request->cookies->get('filtre-campus');
        if ($filtreCampusId) {
            $campus = $campusRepository->find($filtreCampusId);
        }
        if (!$campus) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $campus = $user->getCampus();
        }
        $filtreForm = $this->createForm(FiltreSortiesType::class, ["campus" => $campus]);
        $filtreForm->handleRequest($request);
        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $campus = $filtreForm->get("campus")->getData();
        }
        $sorties = $sortieRepository->findBy(["campus" => $campus]);
        $response = $this->render('main/home.html.twig', [
            "filtreForm" => $filtreForm,
            "sorties" => $sorties,
        ]);
        $cookie = new Cookie("filtre-campus", $campus->getId(), strtotime('+1 day'));
        $response->headers->setCookie($cookie);
        return $response;
    }
}
