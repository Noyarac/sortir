<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CampusType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/sortie")]
final class SortieController extends AbstractController
{
    #[Route('/{id}', name: 'sortie_details', requirements: ["id" => "\d+"])]
    public function details(Sortie $sortie): Response
    {
        return $this->render('sortie/details.html.twig', [
            "sortie" => $sortie,
        ]);
    }

}
