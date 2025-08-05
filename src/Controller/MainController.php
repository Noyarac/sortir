<?php

namespace App\Controller;

use App\Form\CampusType;
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
        $campusForm = $this->createForm(CampusType::class);
        $campusForm->handleRequest($request);
        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $campus = $campusForm->get("campus")->getData();
            $sorties = $sortieRepository->findBy(["campus" => $campus]);
        } else {
            $sorties = [];
        }
        return $this->render('main/home.html.twig', [
            "campusForm" => $campusForm,
            "sorties" => $sorties,
        ]);
    }
}
