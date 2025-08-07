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
    public function gererEtatSortie(Sortie $sortie, Request $request) : void
    {
        if($request->request->has(Etat::EN_CREATION->value)) {
            $sortie->setEtat(Etat::EN_CREATION->value);
            $this->entityManager->persist($sortie);
        }

        if($request->request->has(Etat::OUVERTE->value)){
            $sortie->setEtat(Etat::OUVERTE->value);
        }

        $this->entityManager->flush();
    }


}
