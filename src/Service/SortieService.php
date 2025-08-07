<?php

namespace App\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
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


}
