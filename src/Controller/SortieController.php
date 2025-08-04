<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/sortie")]
final class SortieController extends AbstractController
{
    #[Route('/{id}', name: 'sortie_list')]
    public function getSortiesListByCampus(Campus $campus, SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findBy(["campus" => $campus]);
        return $this->render('sortie/list.html.twig', [
            "campus" => $campus,
            "sorties" => $sorties,
        ]);
    }
}
