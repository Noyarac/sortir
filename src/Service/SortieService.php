<?php

namespace App\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SortieService
{

    public function __construct(private readonly EntityManagerInterface $entityManager){}
    public function gererEtatSortie(Sortie $sortie, ?string $etat = null) : void
    {
        if ($etat !== null) {
            $sortie->setEtat($etat);
        }

        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    public function inscription(Sortie $sortie, User $user): bool {
        try {
            $sortie->addParticipant($user);
            if (sizeof($sortie->getParticipants()) >= $sortie->getNbInscriptionMax()) {
                $sortie->setEtat(Etat::CLOTUREE->value);
            }
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            return false;
        }
        return true;
    }

}
