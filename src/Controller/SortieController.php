<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CampusType;
use App\Repository\SortieRepository;
use App\Security\Voter\SortieVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    #[Route('/{id}/inscription', name: 'sortie_inscription', requirements: ["id" => "\d+"])]
    #[IsGranted(SortieVoter::INSCRIPTION, 'sortie')]
    public function inscription(Sortie $sortie, UserInterface $user, EntityManagerInterface $em): Response
    {
        $sortie->addParticipant($user);
        $em->persist($sortie);
        $em->flush();
        dd($sortie);
        return $this->redirectToRoute("main_home");
    }

}
