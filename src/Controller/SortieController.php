<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CampusType;
use App\Form\SortieType;
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
    #[Route('/creation', name: 'sortie_creation', requirements: ["id" => "\d+"])]
    public function creationSortie(Request $request): Response
    {
        $sortie = new Sortie();
        //Inutile de vérifier que $user existe car application entièrement protégée et seulement accessible à ROLE_USER
        $user = $this->getUser();
        $sortie->setOrganisateur($user);
        $sortie->setCampus($user->getCampus());

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        return $this->render('sortie/creation.html.twig', [
            'sortieForm' => $sortieForm,
        ]);
    }

}
