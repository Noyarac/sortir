<?php

namespace App\Controller;

use App\Form\FiltreSortiesType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ["GET", "POST"])]
    public function home(Request $request, SortieRepository $sortieRepository): Response
    {
        $filtreForm = $this->createForm(FiltreSortiesType::class);
        $filtreForm->handleRequest($request);
        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $campus = $filtreForm->get("campus")->getData();
            $sorties = $sortieRepository->findBy(["campus" => $campus]);
        } else {
            $sorties = [];
        }
        return $this->render('main/home.html.twig', [
            "filtreForm" => $filtreForm,
            "sorties" => $sorties,
        ]);
    }
}
