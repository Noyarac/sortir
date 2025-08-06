<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\FiltreSortiesType;
use App\Security\Voter\SortieVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route("/sortie")]
final class SortieController extends AbstractController
{
    #[Route('/{id}', name: 'sortie_details', requirements: ["id" => "\d+"], methods: ["GET"])]
    public function details(Sortie $sortie): Response
    {
        return $this->render('sortie/details.html.twig', [
            "sortie" => $sortie,
        ]);
    }

    #[Route('/{id}/inscription', name: 'sortie_inscription', requirements: ["id" => "\d+"], methods: ["POST"])]
    public function inscription(Sortie $sortie, UserInterface $user, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted(SortieVoter::INSCRIPTION, $sortie)) {
            $this->addFlash("danger", "Il n'est pas possible de s'inscrire à cette sortie");
            return $this->redirectToRoute("main_home");
        }
        $sortie->addParticipant($user);
        $em->persist($sortie);
        $em->flush();
        $this->addFlash("success", "Vous vous êtes inscrit(e) avec succès.");
        return $this->redirectToRoute("main_home");
    }

    #[Route('/{id}/desistement', name: 'sortie_desistement', requirements: ["id" => "\d+"], methods: ["POST"])]
    public function desistement(Sortie $sortie, UserInterface $user, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted(SortieVoter::DESISTEMENT, $sortie)) {
            $this->addFlash("danger", "Il n'est pas possible de se désister de cette sortie");
            return $this->redirectToRoute("main_home");
        }
        $sortie->removeParticipant($user);
        $em->persist($sortie);
        $em->flush();
        $this->addFlash("success", "Vous vous êtes désisté(e) avec succès.");
        return $this->redirectToRoute("main_home");
    }

}
